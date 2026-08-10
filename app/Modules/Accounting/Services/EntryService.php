<?php

namespace App\Modules\Accounting\Services;

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Models\AccountingEntryLine;
use App\Modules\Accounting\Services\Exceptions\EntryLockedException;
use App\Modules\Accounting\Services\Exceptions\UnbalancedEntryException;
use Illuminate\Support\Facades\DB;

class EntryService
{
    /**
     * Crée une écriture en brouillon avec ses lignes.
     */
    public function createDraft(Company $company, array $data, array $linesData): AccountingEntry
    {
        return DB::transaction(function () use ($company, $data, $linesData) {
            $entry = AccountingEntry::create([
                'company_id' => $company->id,
                'journal_id' => $data['journal_id'],
                'period_id' => $data['period_id'],
                'reference' => $data['reference'] ?? null,
                'entry_date' => $data['entry_date'],
                'description' => $data['description'],
                'status' => AccountingEntry::STATUS_DRAFT,
            ]);

            $sort = 0;
            foreach ($linesData as $line) {
                AccountingEntryLine::create([
                    'company_id' => $company->id,
                    'entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'third_party_type' => $line['third_party_type'] ?? null,
                    'third_party_id' => $line['third_party_id'] ?? null,
                    'sort_order' => ++$sort,
                ]);
            }

            activity()
                ->performedOn($entry)
                ->causedBy(auth()->user())
                ->withProperties(['lines_count' => count($linesData)])
                ->log('Création écriture brouillon');

            return $entry->load('lines.account');
        });
    }

    /**
     * Valide une écriture : vérifie l'équilibre et verrouille.
     */
    public function validate(AccountingEntry $entry, User $user): AccountingEntry
    {
        if (! $entry->isDraft()) {
            throw new EntryLockedException('Seules les écritures brouillon peuvent être validées.');
        }

        // Rechargement pour avoir les totaux à jour
        $entry->load('lines');

        $totalDebit = (float) $entry->lines->sum('debit');
        $totalCredit = (float) $entry->lines->sum('credit');

        if (abs($totalDebit - $totalCredit) >= 0.01) {
            throw new UnbalancedEntryException($totalDebit, $totalCredit);
        }

        return DB::transaction(function () use ($entry, $user) {
            // Génération du numéro de pièce via le journal
            $entryNumber = $entry->journal->generateNextNumber();

            $entry->update([
                'entry_number' => $entryNumber,
                'status' => AccountingEntry::STATUS_VALIDATED,
                'is_locked' => true,
                'validated_by' => $user->id,
                'validated_at' => now(),
            ]);

            activity()
                ->performedOn($entry)
                ->causedBy($user)
                ->withProperties(['entry_number' => $entryNumber])
                ->log('Validation écriture comptable');

            return $entry;
        });
    }

    /**
     * Crée une écriture de contre-passation (extourne).
     * Inverse les débits et crédits pour corriger une erreur.
     */
    public function reverse(AccountingEntry $entry, User $user, string $reason): AccountingEntry
    {
        if (! $entry->isValidated()) {
            throw new EntryLockedException('Seules les écritures validées peuvent être contre-passées.');
        }

        return DB::transaction(function () use ($entry, $user, $reason) {
            $entry->load('lines');

            // 1. Créer la nouvelle écriture inverse
            $reversal = AccountingEntry::create([
                'company_id' => $entry->company_id,
                'journal_id' => $entry->journal_id,
                'period_id' => $entry->period_id,
                'reference' => 'EXT-' . ($entry->reference ?? $entry->id),
                'entry_date' => now()->toDateString(),
                'description' => "Contre-passation de {$entry->entry_number} : {$reason}",
                'status' => AccountingEntry::STATUS_DRAFT,
                'reversal_of_id' => $entry->id,
            ]);

            // 2. Inverser les lignes (débit devient crédit et vice-versa)
            foreach ($entry->lines as $line) {
                AccountingEntryLine::create([
                    'company_id' => $entry->company_id,
                    'entry_id' => $reversal->id,
                    'account_id' => $line->account_id,
                    'description' => $line->description,
                    'debit' => $line->credit, // Inversion
                    'credit' => $line->debit, // Inversion
                    'third_party_type' => $line->third_party_type,
                    'third_party_id' => $line->third_party_id,
                    'sort_order' => $line->sort_order,
                ]);
            }

            // 3. Marquer l'écriture d'origine comme annulée
            $entry->update([
                'status' => AccountingEntry::STATUS_CANCELLED,
                'reversed_by_id' => $reversal->id,
                'cancelled_by' => $user->id,
                'cancelled_at' => now(),
            ]);

            // 4. Valider automatiquement la contre-passation
            $this->validate($reversal, $user);

            activity()
                ->performedOn($entry)
                ->causedBy($user)
                ->withProperties(['reversal_id' => $reversal->id, 'reason' => $reason])
                ->log('Contre-passation écriture');

            return $reversal;
        });
    }
}
