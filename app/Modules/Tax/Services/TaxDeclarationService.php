<?php

namespace App\Modules\Tax\Services;

use App\Models\Company;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Tax\Models\Tax;
use App\Modules\Tax\Models\TaxDeclaration;
use Illuminate\Support\Facades\DB;

class TaxDeclarationService
{
    /**
     * Génère les déclarations mensuelles (TVA + Taxe sur salaires) pour une période YYYY-MM
     */
    public function generateForPeriod(Company $company, string $period): array
    {
        $start = $period . '-01';
        $end = date('Y-m-t', strtotime($start));
        $dueDate = date('Y-m-15', strtotime($start . ' +1 month'));
        $created = [];

        // ---- 1. TVA (collectée 443 - déductible 445) ----
        $collected = $this->accountSum($company, '443', $start, $end, 'credit');
        $deductible = $this->accountSum($company, '445', $start, $end, 'debit');
        $vatDue = max(0, $collected - $deductible);

        if ($collected > 0 || $vatDue > 0) {
            $vatRate = $this->rate($company, 'TVA_18', $start) ?? 18;
            $base = $vatRate > 0 ? round($collected * 100 / $vatRate, 2) : 0;

            $created[] = TaxDeclaration::updateOrCreate(
                ['company_id' => $company->id, 'type' => 'vat', 'period' => $period],
                [
                    'reference' => 'TVA-' . str_replace('-', '', $period),
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'base_amount' => $base,
                    'tax_amount' => round($vatDue, 2),
                ]
            );
        }

        // ---- 2. Taxe sur salaires (base = masse salariale brute) ----
        $gross = (float) Payslip::where('company_id', $company->id)
            ->whereBetween('period_start', [$start, $end])
            ->sum('gross_salary');

        if ($gross > 0) {
            $tsRate = $this->rate($company, 'TS', $start) ?? 4.5;

            $created[] = TaxDeclaration::updateOrCreate(
                ['company_id' => $company->id, 'type' => 'tax_on_salaries', 'period' => $period],
                [
                    'reference' => 'TS-' . str_replace('-', '', $period),
                    'due_date' => $dueDate,
                    'status' => 'pending',
                    'base_amount' => $gross,
                    'tax_amount' => round($gross * $tsRate / 100, 2),
                ]
            );
        }

        return $created;
    }

    /**
     * Génère la déclaration IS annuelle (si bénéfice)
     */
    public function generateCorporateIncomeTax(Company $company, int $year): ?TaxDeclaration
    {
        $pnl = app(\App\Modules\Reporting\Services\AccountingReportService::class)->getProfitAndLoss($company);
        $net = (float) $pnl['net_income'];

        if ($net <= 0) return null;

        $isRate = $this->rate($company, 'IS', $year . '-12-31') ?? 27;

        return TaxDeclaration::updateOrCreate(
            ['company_id' => $company->id, 'type' => 'corporate_income_tax', 'period' => (string) $year],
            [
                'reference' => 'IS-' . $year,
                'due_date' => ($year + 1) . '-04-30',
                'status' => 'pending',
                'base_amount' => $net,
                'tax_amount' => round($net * $isRate / 100, 2),
            ]
        );
    }

    /**
     * Solde d'un compte (préfixe) sur une période
     */
    protected function accountSum(Company $company, string $prefix, string $start, string $end, string $side): float
    {
        $query = DB::table('journal_items')
            ->join('journal_entries', 'journal_items.journal_entry_id', '=', 'journal_entries.id')
            ->join('accounts', 'journal_items.account_id', '=', 'accounts.id')
            ->where('journal_entries.company_id', $company->id)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$start, $end])
            ->where('accounts.number', 'like', $prefix . '%');

        return (float) ($side === 'credit'
            ? $query->sum(DB::raw('journal_items.credit - journal_items.debit'))
            : $query->sum(DB::raw('journal_items.debit - journal_items.credit')));
    }

    /**
     * Taux actif à une date (moteur de règles à dates d'effet)
     */
    protected function rate(Company $company, string $code, string $date): ?float
    {
        $tax = Tax::where('company_id', $company->id)->where('code', $code)->first();
        if (!$tax) return null;

        $rate = $tax->rates()
            ->where('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>=', $date))
            ->where('is_active', true)
            ->orderByDesc('effective_from')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }
}
