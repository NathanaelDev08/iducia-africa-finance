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
                'alerts' => array_merge($alerts, $this->overdueInvoiceAlerts($company)),
                'recent_invoices' => $this->recentInvoices($company),
                'recent_receipts' => $this->recentReceipts($company),
                'recent_purchases' => $this->recentPurchases($company),
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

    protected function overdueInvoiceAlerts(Company $company): array
    {
        if (!Schema::hasTable('sales_invoices') || !Schema::hasTable('clients')) {
            return [];
        }

        $results = DB::table('sales_invoices')
            ->leftJoin('clients', 'sales_invoices.client_id', '=', 'clients.id')
            ->where('sales_invoices.company_id', $company->id)
            ->whereNotIn('sales_invoices.status', ['paid'])
            ->whereNotNull('sales_invoices.due_date')
            ->where('sales_invoices.due_date', '<', now()->toDateString())
            ->orderBy('sales_invoices.due_date')
            ->limit(5)
            ->get(['sales_invoices.reference', 'sales_invoices.due_date', 'sales_invoices.total_ttc', 'clients.name as client_name']);

        return $results->map(fn ($row) => '⚠ Facture ' . ($row->reference ?: '?') . ' de ' . ($row->client_name ?: '—') . ' échue le ' . $row->due_date . ' (' . number_format((float) $row->total_ttc, 0, ',', ' ') . ' F)')->toArray();
    }

    protected function recentInvoices(Company $company): array
    {
        if (!Schema::hasTable('sales_invoices') || !Schema::hasTable('clients')) {
            return [];
        }

        return DB::table('sales_invoices')
            ->leftJoin('clients', 'sales_invoices.client_id', '=', 'clients.id')
            ->where('sales_invoices.company_id', $company->id)
            ->orderByDesc('sales_invoices.id')
            ->limit(6)
            ->get(['sales_invoices.id', 'sales_invoices.reference', 'sales_invoices.invoice_date', 'sales_invoices.total_ttc', 'sales_invoices.status', 'clients.name as client_name'])
            ->map(fn ($i) => [
                'id' => $i->id,
                'reference' => $i->reference ?: 'FAC-' . $i->id,
                'client' => $i->client_name ?: '—',
                'date' => $i->invoice_date,
                'total' => (float) $i->total_ttc,
                'status' => $i->status ?: 'pending',
            ])
            ->toArray();
    }

    protected function recentReceipts(Company $company): array
    {
        if (!Schema::hasTable('customer_payments') || !Schema::hasTable('clients')) {
            return [];
        }

        return DB::table('customer_payments')
            ->leftJoin('clients', 'customer_payments.client_id', '=', 'clients.id')
            ->where('customer_payments.company_id', $company->id)
            ->orderByDesc('customer_payments.id')
            ->limit(6)
            ->get(['customer_payments.id', 'customer_payments.reference', 'customer_payments.payment_date', 'customer_payments.amount', 'clients.name as client_name'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'reference' => $p->reference ?: 'REC-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT),
                'client' => $p->client_name ?: '—',
                'date' => $p->payment_date,
                'amount' => (float) $p->amount,
                'method' => $p->payment_method ?? 'Espèces',
            ])
            ->toArray();
    }

    protected function recentPurchases(Company $company): array
    {
        if (!Schema::hasTable('purchase_invoices') || !Schema::hasTable('suppliers')) {
            return [];
        }

        return DB::table('purchase_invoices')
            ->leftJoin('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
            ->where('purchase_invoices.company_id', $company->id)
            ->orderByDesc('purchase_invoices.id')
            ->limit(6)
            ->get(['purchase_invoices.id', 'purchase_invoices.reference', 'purchase_invoices.invoice_date', 'purchase_invoices.total_ttc', 'purchase_invoices.status', 'suppliers.name as supplier_name'])
            ->map(fn ($p) => [
                'id' => $p->id,
                'reference' => $p->reference ?: 'FAF-' . $p->id,
                'supplier' => $p->supplier_name ?: '—',
                'date' => $p->invoice_date,
                'total' => (float) $p->total_ttc,
                'status' => $p->status ?: 'pending',
            ])
            ->toArray();
    }
}
