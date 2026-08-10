<?php

namespace App\Modules\Reporting\Services;

use App\Models\Company;
use App\Modules\Accounting\Models\AccountingEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    public function __construct(protected AccountingReportService $reportService)
    {
    }

    public function getOverview(Company $company): array
    {
        $defaultMetrics = [
            'revenue_year' => 0, 'expenses_year' => 0, 'net_result_year' => 0,
            'treasury' => 0, 'receivables' => 0, 'payables' => 0,
            'payroll_mass_current_month' => 0, 'active_employees' => 0,
        ];

        try {
            $yearStart = now()->startOfYear()->toDateString();
            $yearEnd = now()->endOfYear()->toDateString();

            $revenue = $this->sumClass($company, 7, 'credit', $yearStart, $yearEnd);
            $expenses = $this->sumClass($company, 6, 'debit', $yearStart, $yearEnd);

            $rows = $this->reportService->balanceRows($company, []);

            $treasury = 0;
            $receivables = 0;
            $payables = 0;

            foreach ($rows as $row) {
                $balance = (float) $row->total_debit - (float) $row->total_credit;

                if (in_array($row->type, ['bank', 'cash'])) {
                    $treasury += $balance;
                }

                if (str_starts_with($row->number, '41')) {
                    $receivables += $balance;
                }

                if (str_starts_with($row->number, '40')) {
                    $payables += -$balance;
                }
            }

            $employeeCount = 0;
            $alerts = [];

            if (Schema::hasTable('employees')) {
                $employeeCount = DB::table('employees')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->whereNull('deleted_at')
                    ->count();
            }

            if (Schema::hasTable('employee_contracts')) {
                $contractsEnding = DB::table('employee_contracts')
                    ->where('company_id', $company->id)
                    ->where('status', 'active')
                    ->whereNotNull('end_date')
                    ->whereBetween('end_date', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ])
                    ->count();

                if ($contractsEnding > 0) {
                    $alerts[] = [
                        'type' => 'hr_contract_end',
                        'message' => "{$contractsEnding} contrat(s) arrivent à échéance dans 30 jours.",
                    ];
                }
            }

            if (Schema::hasTable('employee_documents')) {
                $expiredDocuments = DB::table('employee_documents')
                    ->where('company_id', $company->id)
                    ->whereNotNull('expires_at')
                    ->where('expires_at', '<', now()->toDateString())
                    ->count();

                if ($expiredDocuments > 0) {
                    $alerts[] = [
                        'type' => 'hr_document_expired',
                        'message' => "{$expiredDocuments} document(s) employé expiré(s).",
                    ];
                }
            }

            $payrollMass = 0;

            if (Schema::hasTable('pay_runs') && Schema::hasTable('payslips')) {
                $payRunIds = DB::table('pay_runs')
                    ->where('company_id', $company->id)
                    ->where('period_start', '<=', now()->toDateString())
                    ->where('period_end', '>=', now()->toDateString())
                    ->pluck('id');

                if ($payRunIds->isNotEmpty()) {
                    $payrollMass = (float) DB::table('payslips')
                        ->whereIn('pay_run_id', $payRunIds)
                        ->sum('gross_salary');
                }
            }

            $upcomingDeadlines = [];

            if (Schema::hasTable('fiscal_deadlines')) {
                $upcomingDeadlines = DB::table('fiscal_deadlines')
                    ->where('company_id', $company->id)
                    ->where('status', 'pending')
                    ->whereBetween('due_date', [
                        now()->toDateString(),
                        now()->addDays(30)->toDateString(),
                    ])
                    ->orderBy('due_date')
                    ->get()
                    ->map(function ($deadline) {
                        return [
                            'type' => $deadline->type,
                            'name' => $deadline->name,
                            'due_date' => $deadline->due_date,
                        ];
                    })
                    ->toArray();
            }

            return [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'currency' => $company->currency,
                ],
                'generated_at' => now()->toISOString(),
                'metrics' => [
                    'revenue_year' => round($revenue, 2),
                    'expenses_year' => round($expenses, 2),
                    'net_result_year' => round($revenue - $expenses, 2),
                    'treasury' => round($treasury, 2),
                    'receivables' => round($receivables, 2),
                    'payables' => round($payables, 2),
                    'payroll_mass_current_month' => round($payrollMass, 2),
                    'active_employees' => $employeeCount,
                ],
                'alerts' => $alerts,
                'upcoming_fiscal_deadlines' => $upcomingDeadlines,
            ];

        } catch (\Throwable $e) {
            // Si une requête SQL plante, on log l'erreur et on affiche le dashboard avec un message d'alerte
            Log::error('Dashboard calculation error: ' . $e->getMessage());
            
            return [
                'company' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'currency' => $company->currency,
                ],
                'generated_at' => now()->toISOString(),
                'metrics' => $defaultMetrics,
                'alerts' => [
                    [
                        'type' => 'system_error',
                        'message' => 'Erreur de calcul du tableau de bord : ' . $e->getMessage(),
                    ]
                ],
                'upcoming_fiscal_deadlines' => [],
            ];
        }
    }

    protected function sumClass(
        Company $company,
        int $classNumber,
        string $direction,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): float {
        $row = AccountingEntryLine::query()
            ->where('accounting_entry_lines.company_id', $company->id)
            ->join('accounting_entries', 'accounting_entries.id', '=', 'accounting_entry_lines.entry_id')
            ->join('accounts', 'accounts.id', '=', 'accounting_entry_lines.account_id')
            ->where('accounting_entries.status', 'validated')
            ->whereNull('accounting_entries.deleted_at')
            ->where('accounts.class_number', $classNumber)
            ->when($dateFrom, function ($q, $value) {
                $q->where('accounting_entries.entry_date', '>=', $value);
            })
            ->when($dateTo, function ($q, $value) {
                $q->where('accounting_entries.entry_date', '<=', $value);
            })
            ->selectRaw('COALESCE(SUM(accounting_entry_lines.debit), 0) as total_debit')
            ->selectRaw('COALESCE(SUM(accounting_entry_lines.credit), 0) as total_credit')
            ->first();

        if (! $row) {
            return 0;
        }

        $debit = (float) $row->total_debit;
        $credit = (float) $row->total_credit;

        return $direction === 'credit'
            ? round($credit - $debit, 2)
            : round($debit - $credit, 2);
    }
}
