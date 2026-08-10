<?php

namespace App\Modules\Tax\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Models\AccountingEntryLine;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\Period;
use App\Modules\Accounting\Models\Tax;
use App\Modules\Accounting\Services\EntryService;
use App\Modules\Tax\Models\VatDeclaration;
use App\Modules\Tax\Models\VatDeclarationLine;
use Illuminate\Support\Facades\DB;

class VatCalculationService
{
    public function __construct(protected EntryService $entryService)
    {
    }

    /**
     * Calcule la déclaration de TVA pour une période donnée.
     * Se base uniquement sur les écritures VALIDÉES.
     */
    public function calculateDeclaration(Company $company, Period $period): VatDeclaration
    {
        return DB::transaction(function () use ($company, $period) {
            // 1. Récupérer les comptes de TVA
            $vatCollectedAccount = Account::where('company_id', $company->id)
                ->where('number', 'like', '4452%') // TVA collectée
                ->first();

            $vatDeductibleAccount = Account::where('company_id', $company->id)
                ->where('number', 'like', '4431%') // TVA déductible
                ->first();

            if (!$vatCollectedAccount || !$vatDeductibleAccount) {
                throw new \Exception('Comptes de TVA (4452 et 4431) introuvables dans le plan comptable.');
            }

            // 2. Calculer la TVA collectée (crédit du compte 4452)
            $totalVatCollected = AccountingEntryLine::where('company_id', $company->id)
                ->where('account_id', $vatCollectedAccount->id)
                ->whereHas('entry', function ($query) use ($period) {
                    $query->where('period_id', $period->id)
                        ->where('status', 'validated');
                })
                ->sum('credit');

            // 3. Calculer la TVA déductible (débit du compte 4431)
            $totalVatDeductible = AccountingEntryLine::where('company_id', $company->id)
                ->where('account_id', $vatDeductibleAccount->id)
                ->whereHas('entry', function ($query) use ($period) {
                    $query->where('period_id', $period->id)
                        ->where('status', 'validated');
                })
                ->sum('debit');

            // 4. Récupérer le crédit de TVA de la période précédente
            $previousPeriod = Period::where('company_id', $company->id)
                ->where('number', $period->number - 1)
                ->where('fiscal_year_id', $period->fiscal_year_id)
                ->first();

            $vatCreditPrevious = 0;
            if ($previousPeriod) {
                $previousDeclaration = VatDeclaration::where('company_id', $company->id)
                    ->where('period_id', $previousPeriod->id)
                    ->first();

                if ($previousDeclaration) {
                    $vatCreditPrevious = (float) $previousDeclaration->vat_credit_to_carry;
                }
            }

            // 5. Calculer la TVA nette
            $totalDeductibleWithCredit = $totalVatDeductible + $vatCreditPrevious;
            $vatToPay = max(0, $totalVatCollected - $totalDeductibleWithCredit);
            $vatCreditToCarry = max(0, $totalDeductibleWithCredit - $totalVatCollected);

            // 6. Créer ou mettre à jour la déclaration
            $declaration = VatDeclaration::updateOrCreate([
                'company_id' => $company->id,
                'period_id' => $period->id,
            ], [
                'reference' => 'TVA-' . $period->name,
                'name' => 'Déclaration TVA ' . $period->name,
                'period_start' => $period->start_date,
                'period_end' => $period->end_date,
                'due_date' => $period->end_date->copy()->addDays(15), // 15 jours après fin de période
                'total_sales_ht' => 0, // À calculer depuis les lignes de ventes si besoin
                'total_vat_collected' => $totalVatCollected,
                'total_purchases_ht' => 0,
                'total_vat_deductible' => $totalVatDeductible,
                'vat_credit_previous' => $vatCreditPrevious,
                'vat_to_pay' => $vatToPay,
                'vat_credit_to_carry' => $vatCreditToCarry,
                'status' => 'calculated',
            ]);

            // 7. Enregistrer les lignes de détail
            $declaration->lines()->delete();

            if ($totalVatCollected > 0) {
                VatDeclarationLine::create([
                    'vat_declaration_id' => $declaration->id,
                    'type' => 'collected',
                    'description' => 'TVA collectée sur ventes',
                    'base_amount' => 0,
                    'tax_rate' => 18,
                    'tax_amount' => $totalVatCollected,
                ]);
            }

            if ($totalVatDeductible > 0) {
                VatDeclarationLine::create([
                    'vat_declaration_id' => $declaration->id,
                    'type' => 'deductible',
                    'description' => 'TVA déductible sur achats',
                    'base_amount' => 0,
                    'tax_rate' => 18,
                    'tax_amount' => $totalVatDeductible,
                ]);
            }

            return $declaration;
        });
    }

    /**
     * Génère l'écriture comptable de TVA.
     */
    public function generateAccountingEntry(VatDeclaration $declaration, User $user): void
    {
        if ($declaration->isLocked()) {
            throw new \Exception('Cette déclaration est déjà verrouillée.');
        }

        $company = $declaration->company;

        // Récupérer les comptes
        $vatCollectedAccount = Account::where('company_id', $company->id)
            ->where('number', 'like', '4452%')
            ->first();

        $vatDeductibleAccount = Account::where('company_id', $company->id)
            ->where('number', 'like', '4431%')
            ->first();

        $vatToPayAccount = Account::where('company_id', $company->id)
            ->where('number', 'like', '4457%') // TVA à décaisser
            ->first();

        $vatCreditAccount = Account::where('company_id', $company->id)
            ->where('number', 'like', '4458%') // TVA à régulariser / Crédit
            ->first();

        if (!$vatCollectedAccount || !$vatDeductibleAccount || !$vatToPayAccount || !$vatCreditAccount) {
            throw new \Exception('Comptes de TVA manquants.');
        }

        // Journal TVA
        $journal = Journal::where('company_id', $company->id)
            ->where('code', 'TV')
            ->first();

        if (!$journal) {
            throw new \Exception('Journal TVA introuvable.');
        }

        $lines = [];

        // Débit 4452 : TVA collectée (solde du compte)
        if ($declaration->total_vat_collected > 0) {
            $lines[] = [
                'account_id' => $vatCollectedAccount->id,
                'description' => 'Solde TVA collectée ' . $declaration->name,
                'debit' => $declaration->total_vat_collected,
                'credit' => 0,
            ];
        }

        // Crédit 4431 : TVA déductible (solde du compte)
        if ($declaration->total_vat_deductible > 0) {
            $lines[] = [
                'account_id' => $vatDeductibleAccount->id,
                'description' => 'Solde TVA déductible ' . $declaration->name,
                'debit' => 0,
                'credit' => $declaration->total_vat_deductible,
            ];
        }

        // Crédit 4457 : TVA à décaisser (si TVA collectée > TVA déductible)
        if ($declaration->vat_to_pay > 0) {
            $lines[] = [
                'account_id' => $vatToPayAccount->id,
                'description' => 'TVA à décaisser ' . $declaration->name,
                'debit' => 0,
                'credit' => $declaration->vat_to_pay,
            ];
        }

        // Débit 4458 : Crédit de TVA à reporter (si TVA déductible > TVA collectée)
        if ($declaration->vat_credit_to_carry > 0) {
            $lines[] = [
                'account_id' => $vatCreditAccount->id,
                'description' => 'Crédit de TVA à reporter ' . $declaration->name,
                'debit' => $declaration->vat_credit_to_carry,
                'credit' => 0,
            ];
        }

        // Créer l'écriture
        $entry = $this->entryService->createDraft($company, [
            'journal_id' => $journal->id,
            'period_id' => $declaration->period_id,
            'reference' => $declaration->reference,
            'entry_date' => $declaration->period_end->toDateString(),
            'description' => 'Écriture de TVA - ' . $declaration->name,
        ], $lines);

        // Valider l'écriture
        $this->entryService->validate($entry, $user);

        // Lier l'écriture à la déclaration
        $declaration->update([
            'accounting_entry_id' => $entry->id,
            'status' => 'validated',
            'is_locked' => true,
            'validated_by' => $user->id,
            'validated_at' => now(),
        ]);
    }
}
