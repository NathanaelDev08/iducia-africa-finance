<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->attributes->get('company') ?? Company::first();
        $cid = $company ? $company->id : 0;

        $m = [
            'revenue' => 0, 'expenses' => 0, 'cash' => 0,
            'clients' => 0, 'suppliers' => 0,
            'invoices_pending' => 0, 'invoices_pending_total' => 0,
            'receipts_count' => 0, 'receipts_total' => 0,
            'employees' => 0, 'payslips' => 0,
        ];

        try {
            if (Schema::hasTable('sales_invoices')) {
                $m['revenue'] = (float) DB::table('sales_invoices')->where('company_id', $cid)->sum('total_ht');
                $m['invoices_pending'] = (int) DB::table('sales_invoices')->where('company_id', $cid)->whereNotIn('status', ['paid'])->count();
                $m['invoices_pending_total'] = (float) DB::table('sales_invoices')->where('company_id', $cid)->whereNotIn('status', ['paid'])->sum('total_ttc');
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('purchase_invoices')) {
                $m['expenses'] = (float) DB::table('purchase_invoices')->where('company_id', $cid)->sum('total_ht');
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('customer_payments')) {
                $m['receipts_count'] = (int) DB::table('customer_payments')->where('company_id', $cid)->count();
                $m['receipts_total'] = (float) DB::table('customer_payments')->where('company_id', $cid)->sum('amount');
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('clients')) $m['clients'] = (int) DB::table('clients')->where('company_id', $cid)->count();
            if (Schema::hasTable('suppliers')) $m['suppliers'] = (int) DB::table('suppliers')->where('company_id', $cid)->count();
            if (Schema::hasTable('employees')) $m['employees'] = (int) DB::table('employees')->where('company_id', $cid)->where('status', 'active')->count();
            if (Schema::hasTable('payslips')) $m['payslips'] = (int) DB::table('payslips')->where('company_id', $cid)->count();
        } catch (\Throwable $e) {}

        // Trésorerie : classe 5 du journal
        try {
            foreach (['journal_items', 'journal_entry_lines'] as $lt) {
                if (!Schema::hasTable($lt) || !Schema::hasTable('journal_entries')) continue;
                $cols = Schema::getColumnListing($lt);
                $fk = in_array('entry_id', $cols) ? 'entry_id' : (in_array('journal_entry_id', $cols) ? 'journal_entry_id' : null);
                if (!$fk || !in_array('account_number', $cols)) continue;
                $m['cash'] = (float) DB::table($lt)
                    ->join('journal_entries', $lt . '.' . $fk, '=', 'journal_entries.id')
                    ->where('journal_entries.company_id', $cid)
                    ->where($lt . '.account_number', 'like', '5%')
                    ->sum(DB::raw($lt . '.debit - ' . $lt . '.credit'));
                break;
            }
        } catch (\Throwable $e) {}

        // Listes récentes
        $recentInvoices = collect(); $recentReceipts = collect(); $recentPurchases = collect(); $alerts = collect();

        try {
            if (Schema::hasTable('sales_invoices')) {
                $recentInvoices = DB::table('sales_invoices')
                    ->leftJoin('clients', 'sales_invoices.client_id', '=', 'clients.id')
                    ->where('sales_invoices.company_id', $cid)
                    ->orderByDesc('sales_invoices.id')->limit(6)
                    ->get(['sales_invoices.*', 'clients.name as client_name'])
                    ->map(fn ($i) => [
                        'id' => $i->id, 'reference' => $i->reference ?? $i->number ?? ('FAC-' . $i->id),
                        'client' => $i->client_name ?? '—', 'date' => $i->invoice_date ?? null,
                        'total' => (float) $i->total_ttc, 'status' => $i->status ?? 'pending',
                    ]);

                // Alertes : factures en retard
                $alerts = DB::table('sales_invoices')
                    ->leftJoin('clients', 'sales_invoices.client_id', '=', 'clients.id')
                    ->where('sales_invoices.company_id', $cid)
                    ->whereNotIn('sales_invoices.status', ['paid'])
                    ->whereNotNull('sales_invoices.due_date')
                    ->where('sales_invoices.due_date', '<', now()->toDateString())
                    ->limit(5)->get(['sales_invoices.id', 'sales_invoices.reference', 'sales_invoices.due_date', 'sales_invoices.total_ttc', 'clients.name as client_name'])
                    ->map(fn ($i) => '⚠️ Facture ' . ($i->reference ?? $i->id) . ' de ' . ($i->client_name ?? '—') . ' échue le ' . $i->due_date . ' (' . number_format((float) $i->total_ttc, 0, ',', ' ') . ' F)');
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('customer_payments')) {
                $recentReceipts = DB::table('customer_payments')
                    ->leftJoin('clients', 'customer_payments.client_id', '=', 'clients.id')
                    ->where('customer_payments.company_id', $cid)
                    ->orderByDesc('customer_payments.id')->limit(6)
                    ->get(['customer_payments.*', 'clients.name as client_name'])
                    ->map(fn ($p) => [
                        'id' => $p->id, 'reference' => $p->reference ?? ('REC-' . str_pad((string) $p->id, 5, '0', STR_PAD_LEFT)),
                        'client' => $p->client_name ?? '—', 'date' => $p->payment_date ?? null,
                        'amount' => (float) $p->amount, 'method' => $p->payment_method ?? 'Virement',
                    ]);
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('purchase_invoices')) {
                $recentPurchases = DB::table('purchase_invoices')
                    ->leftJoin('suppliers', 'purchase_invoices.supplier_id', '=', 'suppliers.id')
                    ->where('purchase_invoices.company_id', $cid)
                    ->orderByDesc('purchase_invoices.id')->limit(5)
                    ->get(['purchase_invoices.*', 'suppliers.name as supplier_name'])
                    ->map(fn ($i) => [
                        'id' => $i->id, 'reference' => $i->reference ?? $i->number ?? ('FAF-' . $i->id),
                        'supplier' => $i->supplier_name ?? '—', 'date' => $i->invoice_date ?? null,
                        'total' => (float) $i->total_ttc, 'status' => $i->status ?? 'pending',
                    ]);
            }
        } catch (\Throwable $e) {}

        // Alertes échéances fiscales
        try {
            if (Schema::hasTable('fiscal_deadlines')) {
                $soon = DB::table('fiscal_deadlines')->where('company_id', $cid)
                    ->where('status', 'pending')
                    ->whereBetween('deadline_date', [now()->toDateString(), now()->addDays(10)->toDateString()])
                    ->get();
                foreach ($soon as $d) {
                    $alerts->push('⏰ ' . $d->name . ' — avant le ' . $d->deadline_date);
                }
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Dashboard', [
            'company' => ['id' => $cid, 'name' => $company->name ?? '—', 'currency' => $company->currency ?? 'FCFA'],
            'metrics' => $m,
            'recentInvoices' => $recentInvoices,
            'recentReceipts' => $recentReceipts,
            'recentPurchases' => $recentPurchases,
            'alerts' => $alerts->values(),
        ]);
    }
}
