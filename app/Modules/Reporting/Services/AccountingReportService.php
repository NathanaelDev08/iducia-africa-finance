<?php

namespace App\Modules\Reporting\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;

class AccountingReportService
{
    public function getTrialBalance(Company $company)
    {
        return DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_items.account_id', '=', 'accounts.id')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'posted')
            ->select(
                'accounts.number', 'accounts.name', 'accounts.class_number', 'accounts.type',
                DB::raw('SUM(journal_items.debit) as total_debit'),
                DB::raw('SUM(journal_items.credit) as total_credit')
            )
            ->groupBy('accounts.id', 'accounts.number', 'accounts.name', 'accounts.class_number', 'accounts.type')
            ->orderBy('accounts.number')
            ->get();
    }

    public function balanceRows(Company $company)
    {
        return $this->getTrialBalance($company);
    }

    public function getProfitAndLoss(Company $company)
    {
        $balance = $this->getTrialBalance($company);

        $expenses = $balance->filter(fn ($row) => (int) $row->class_number === 6 || $row->type === 'expense');
        $revenues = $balance->filter(fn ($row) => (int) $row->class_number === 7 || $row->type === 'revenue');

        $totalExpenses = $expenses->sum(fn ($row) => $row->total_debit - $row->total_credit);
        $totalRevenues = $revenues->sum(fn ($row) => $row->total_credit - $row->total_debit);

        return [
            'expenses' => $expenses->values(),
            'revenues' => $revenues->values(),
            'total_expenses' => $totalExpenses,
            'total_revenues' => $totalRevenues,
            'net_income' => $totalRevenues - $totalExpenses,
        ];
    }

    public function getBalanceSheet(Company $company)
    {
        $balance = $this->getTrialBalance($company);
        $pnl = $this->getProfitAndLoss($company);

        $assets = $balance->filter(fn ($row) => in_array($row->type, ['asset', 'bank', 'cash', 'customer']) && ($row->total_debit - $row->total_credit) >= 0);
        $liabilities = $balance->filter(fn ($row) => in_array($row->type, ['liability', 'supplier', 'tax', 'equity']) && ($row->total_credit - $row->total_debit) >= 0);

        $totalAssets = $assets->sum(fn ($row) => $row->total_debit - $row->total_credit);
        $totalLiabilities = $liabilities->sum(fn ($row) => $row->total_credit - $row->total_debit) + $pnl['net_income'];

        return [
            'assets' => $assets->values(),
            'liabilities' => $liabilities->values(),
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_income' => $pnl['net_income'],
        ];
    }

    /**
     * Données pour les graphiques (12 derniers mois)
     */
    public function getCharts(Company $company)
    {
        $start = now()->subMonths(11)->startOfMonth()->toDateString();
        $monthExpr = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', journal_entries.entry_date)"
            : "TO_CHAR(journal_entries.entry_date, 'YYYY-MM')";

        $base = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_items.account_id', '=', 'accounts.id')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '>=', $start);

        // 1. Performance mensuelle (Classes 6 & 7)
        $monthlyRows = (clone $base)
            ->whereIn('accounts.class_number', [6, 7])
            ->select(
                DB::raw("{$monthExpr} AS month"),
                'accounts.class_number',
                DB::raw('SUM(journal_items.debit) AS total_debit'),
                DB::raw('SUM(journal_items.credit) AS total_credit')
            )
            ->groupBy('month', 'accounts.class_number')
            ->orderBy('month')
            ->get();

        $monthly = [];
        foreach ($monthlyRows as $row) {
            if (!isset($monthly[$row->month])) {
                $monthly[$row->month] = ['month' => $row->month, 'revenus' => 0, 'charges' => 0];
            }
            if ((int) $row->class_number === 7) {
                $monthly[$row->month]['revenus'] += (float) $row->total_credit - (float) $row->total_debit;
            } else {
                $monthly[$row->month]['charges'] += (float) $row->total_debit - (float) $row->total_credit;
            }
        }

        // 2. Répartition des charges (Classe 6)
        $expenseBreakdown = (clone $base)
            ->where('accounts.class_number', 6)
            ->select('accounts.name AS name', DB::raw('SUM(journal_items.debit - journal_items.credit) AS value'))
            ->groupBy('accounts.id', 'accounts.name')
            ->havingRaw('SUM(journal_items.debit - journal_items.credit) > 0')
            ->get()
            ->map(fn ($r) => ['name' => $r->name, 'value' => round((float) $r->value, 2)])
            ->values()->all();

        // 3. Trésorerie cumulée (Classe 5)
        $cashRows = (clone $base)
            ->where('accounts.class_number', 5)
            ->select(
                DB::raw("{$monthExpr} AS month"),
                DB::raw('SUM(journal_items.debit) AS total_debit'),
                DB::raw('SUM(journal_items.credit) AS total_credit')
            )
            ->groupBy('month')->orderBy('month')->get();

        $cashflow = [];
        $cumul = 0;
        foreach ($cashRows as $row) {
            $cumul += (float) $row->total_debit - (float) $row->total_credit;
            $cashflow[] = ['month' => $row->month, 'tresorerie' => round($cumul, 2)];
        }

        return [
            'monthly' => array_values($monthly),
            'expenseBreakdown' => $expenseBreakdown,
            'cashflow' => $cashflow,
        ];
    }
}
