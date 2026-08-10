<?php
namespace App\Modules\Assets\Services;

use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Assets\Models\Asset;
use App\Modules\Assets\Models\AssetDepreciation;
use Illuminate\Support\Facades\DB;

class AssetDepreciationService
{
    /**
     * Génère les dotations d'amortissement pour une période YYYY-MM (brouillons)
     */
    public function generateForPeriod($company, string $period): int
    {
        $created = 0;
        $assets = Asset::where('company_id', $company->id)->where('status', 'active')->get();
        $lastDay = date('Y-m-t', strtotime($period . '-01'));

        foreach ($assets as $asset) {
            // Ne pas doubler
            if (AssetDepreciation::where('asset_id', $asset->id)->where('period', $period)->exists()) continue;
            // Actif acquis après la période ?
            if ($asset->acquisition_date->format('Y-m') > $period) continue;

            $monthly = $asset->monthlyDepreciation();
            if ($monthly <= 0) continue;

            $accumulated = (float) AssetDepreciation::where('asset_id', $asset->id)->where('period', '<', $period)->sum('amount');
            $depreciable = (float) $asset->acquisition_cost - (float) $asset->residual_value;
            // Plafonner au montant amortissable
            $amount = min($monthly, max(0, $depreciable - $accumulated));
            if ($amount <= 0) continue;

            AssetDepreciation::create([
                'company_id' => $company->id,
                'asset_id' => $asset->id,
                'period' => $period,
                'depreciation_date' => $lastDay,
                'amount' => $amount,
                'accumulated' => $accumulated + $amount,
                'net_book_value' => (float) $asset->acquisition_cost - ($accumulated + $amount),
                'status' => 'draft',
            ]);
            $created++;
        }
        return $created;
    }

    /**
     * Comptabilise une dotation : 681 Débit / 281 Crédit
     */
    public function postDepreciation(AssetDepreciation $dep): JournalEntry
    {
        if ($dep->accounting_entry_id) throw new \Exception('Dotation déjà comptabilisée.');
        $company = $dep->company;
        $asset = $dep->asset;

        $journal = Journal::firstOrCreate(
            ['company_id' => $company->id, 'code' => 'OD'],
            ['name' => 'Opérations Diverses', 'type' => 'general']
        );

        $expenseAccount = Account::where('company_id', $company->id)->where('number', 'like', ($asset->account_expense ?: '681') . '%')->first()
            ?? Account::where('company_id', $company->id)->where('number', 'like', '68%')->first();
        $deprAccount = Account::where('company_id', $company->id)->where('number', 'like', ($asset->account_depreciation ?: '281') . '%')->first()
            ?? Account::where('company_id', $company->id)->where('number', 'like', '28%')->first();

        if (!$expenseAccount || !$deprAccount) throw new \Exception('Comptes 68/28 introuvables.');

        $entry = JournalEntry::create([
            'company_id' => $company->id, 'journal_id' => $journal->id,
            'entry_date' => $dep->depreciation_date,
            'reference' => 'AMORT-' . $asset->code . '-' . $dep->period,
            'description' => 'Dotation amortissement ' . $asset->name . ' (' . $dep->period . ')',
            'status' => 'draft', 'source_type' => AssetDepreciation::class, 'source_id' => $dep->id,
        ]);

        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $expenseAccount->id,
            'debit' => $dep->amount, 'credit' => 0, 'description' => 'Dotation ' . $asset->name]);
        JournalItem::create(['journal_entry_id' => $entry->id, 'account_id' => $deprAccount->id,
            'debit' => 0, 'credit' => $dep->amount, 'description' => 'Amortissement ' . $asset->name]);

        $entry->validateAndPost();
        $dep->update(['accounting_entry_id' => $entry->id, 'status' => 'posted']);
        return $entry;
    }
}
