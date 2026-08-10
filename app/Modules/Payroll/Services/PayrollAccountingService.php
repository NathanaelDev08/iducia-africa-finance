<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Accounting\Models\Account;
use App\Modules\Payroll\Models\PayRun;
use Illuminate\Support\Facades\DB;

class PayrollAccountingService
{
    public function postPayRunToAccounting(PayRun $payRun): JournalEntry
    {
        if ($payRun->status !== 'calculated' && $payRun->status !== 'validated') {
            throw new \Exception("La paie doit être calculée ou validée avant d'être comptabilisée.");
        }

        if ($payRun->accounting_entry_id) {
            throw new \Exception("Cette période de paie a déjà été comptabilisée.");
        }

        return DB::transaction(function () use ($payRun) {
            $company = $payRun->company;

            // 1. Récupérer les comptes comptables (SYSCOHADA)
            // On utilise des comptes par défaut, à paramétrer finement plus tard
            $accounts = [
                'salaries_expense' => Account::where('company_id', $company->id)->where('number', 'like', '661%')->first(),
                'employer_charges' => Account::where('company_id', $company->id)->where('number', 'like', '664%')->first(),
                'personnel_payable' => Account::where('company_id', $company->id)->where('number', 'like', '421%')->first(),
                'cnps_payable' => Account::where('company_id', $company->id)->where('number', 'like', '433%')->first(), // ou 431
                'tax_on_salaries' => Account::where('company_id', $company->id)->where('number', 'like', '442%')->first(),
            ];

            // Vérification minimale des comptes
            if (!$accounts['salaries_expense'] || !$accounts['personnel_payable']) {
                throw new \Exception("Comptes comptables 661 et 421 introuvables pour l'entreprise {$company->name}.");
            }

            // 2. Calculer les totaux de la paie
            $payslips = $payRun->payslips;
            
            $totalGross = $payslips->sum('gross_salary');
            $totalNet = $payslips->sum('net_salary');
            $totalEmployerCharges = $payslips->sum('employer_contributions');
            
            // On suppose que les retenues CNPS et Impôts sont dans les lignes de bulletins
            $totalCnps = 0;
            $totalTax = 0;
            
            foreach ($payslips as $payslip) {
                foreach ($payslip->items as $item) {
                    if ($item->type === 'employee_contribution') {
                        $totalCnps += $item->amount;
                    }
                    if ($item->type === 'tax') {
                        $totalTax += $item->amount;
                    }
                }
            }

            // 3. Créer l'écriture comptable (Journal Entry)
            // On utilise le Journal "OD" (Opérations Diverses) ou "PA" (Paie)
            $journal = \App\Modules\Accounting\Models\Journal::where('company_id', $company->id)->where('type', 'payroll')->first();
            if (!$journal) {
                $journal = \App\Modules\Accounting\Models\Journal::where('company_id', $company->id)->where('code', 'OD')->first();
            }

            $journalEntry = JournalEntry::create([
                'company_id' => $company->id,
                'journal_id' => $journal->id,
                'entry_date' => $payRun->period_end,
                'reference' => $payRun->reference,
                'description' => "Paie {$payRun->name}",
                'status' => 'posted',
                'source_type' => PayRun::class,
                'source_id' => $payRun->id,
            ]);

            // 4. Créer les lignes d'écriture (Journal Items)
            
            // Débit 661 : Salaires Bruts
            if ($totalGross > 0) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accounts['salaries_expense']->id,
                    'debit' => $totalGross,
                    'credit' => 0,
                    'description' => "Rémunérations dues - {$payRun->name}",
                ]);
            }

            // Débit 664 : Charges Patronales
            if ($totalEmployerCharges > 0 && $accounts['employer_charges']) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accounts['employer_charges']->id,
                    'debit' => $totalEmployerCharges,
                    'credit' => 0,
                    'description' => "Charges sociales patronales - {$payRun->name}",
                ]);
            }

            // Crédit 421 : Net à payer
            if ($totalNet > 0) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accounts['personnel_payable']->id,
                    'debit' => 0,
                    'credit' => $totalNet,
                    'description' => "Personnel - Rémunérations dues - {$payRun->name}",
                ]);
            }

            // Crédit 433 : CNPS (Part salariale + Patronale)
            $totalCnpsToCredit = $totalCnps + $totalEmployerCharges;
            if ($totalCnpsToCredit > 0 && $accounts['cnps_payable']) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accounts['cnps_payable']->id,
                    'debit' => 0,
                    'credit' => $totalCnpsToCredit,
                    'description' => "CNPS à payer - {$payRun->name}",
                ]);
            }

            // Crédit 442 : Impôts sur salaires
            if ($totalTax > 0 && $accounts['tax_on_salaries']) {
                JournalItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $accounts['tax_on_salaries']->id,
                    'debit' => 0,
                    'credit' => $totalTax,
                    'description' => "Impôts sur salaires à payer - {$payRun->name}",
                ]);
            }

            // 5. Vérifier l'équilibre et valider
            $journalEntry->refresh();
            $journalEntry->validateAndPost();

            // Lier l'écriture à la paie
            $payRun->update(['accounting_entry_id' => $journalEntry->id, 'status' => 'posted']);

            return $journalEntry;
        });
    }
}
