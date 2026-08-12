<?php

namespace App\Modules\Tax\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Tax\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('module:tax');
    }

    protected function company(Request $request): Company
    {
        return $request->attributes->get('company') ?? Company::first();
    }

    protected function declarationsTable(): ?string
    {
        if (Schema::hasTable('tax_declarations')) return 'tax_declarations';
        if (Schema::hasTable('vat_declarations')) return 'vat_declarations';
        return null;
    }

    // ═══════════ PAGE PRINCIPALE (3 onglets) ═══════════
    public function index(Request $request)
    {
        $company = $this->company($request);
        $today = now()->toDateString();

        // ── Taxes + taux courant ──
        $taxes = collect();
        try {
            $taxes = Tax::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))
                ->orderBy('code')->get()->map(function ($t) use ($today) {
                    $rates = collect();
                    try { $rates = $t->rates()->orderByDesc('effective_from')->get(); } catch (\Throwable $e) {}
                    $current = $rates->first(fn ($r) => $r->effective_from <= $today && $r->is_active);
                    return [
                        'id' => $t->id,
                        'code' => $t->code,
                        'name' => $t->name,
                        'type' => $t->type,
                        'is_active' => (bool) $t->is_active,
                        'rate' => $current ? (float) $current->rate : null,
                        'effective_from' => isset($current) && $current->effective_from ? $current->effective_from->toDateString() : null,
                        'rates_count' => $rates->count(),
                    ];
                });
        } catch (\Throwable $e) {}

        // ── Déclarations ──
        $declarations = collect();
        $table = $this->declarationsTable();
        if ($table) {
            try {
                $declarations = DB::table($table)
                    ->where('company_id', $company->id)
                    ->orderByDesc('year')->orderByDesc('month')
                    ->limit(60)->get()->map(fn ($d) => [
                        'id' => $d->id,
                        'tax_name' => $d->tax_name ?? ($d->name ?? 'Déclaration'),
                        'year' => $d->year ?? null,
                        'month' => $d->month ?? null,
                        'base_amount' => (float) ($d->base_amount ?? $d->taxable_amount ?? 0),
                        'tax_amount' => (float) ($d->tax_amount ?? $d->vat_amount ?? $d->total_vat ?? $d->vat_due ?? 0),
                        'status' => $d->status ?? 'pending',
                    ]);
            } catch (\Throwable $e) {}
        }

        // ── Échéances ──
        $deadlines = collect();
        if (Schema::hasTable('fiscal_deadlines')) {
            try {
                $deadlines = DB::table('fiscal_deadlines')
                    ->where('company_id', $company->id)
                    ->orderBy('deadline_date')->limit(30)->get()->map(fn ($d) => [
                        'id' => $d->id,
                        'name' => $d->name,
                        'deadline_date' => $d->deadline_date ?? ($d->due_date ?? null),
                        'status' => $d->status ?? 'pending',
                    ]);
            } catch (\Throwable $e) {}
        }

        return Inertia::render('Tax/Index', [
            'taxes' => $taxes,
            'declarations' => $declarations,
            'deadlines' => $deadlines,
            'initialTab' => $request->query('tab', 'taxes'),
        ]);
    }

    // ═══════════ CRUD TAXES ═══════════
    public function storeTax(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('taxes')->where('company_id', $this->company($request)->id)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:vat,withholding,income_tax,ts,other',
            'rate' => 'nullable|numeric|min:0|max:100',
            'effective_from' => 'nullable|date',
        ]);

        try {
            $tax = Tax::create([
                'company_id' => $company->id,
                'code' => $data['code'],
                'name' => $data['name'],
                'type' => $data['type'],
                'scope' => 'company',
                'is_active' => true,
            ]);

            try {
                $tax->rates()->create([
                    'effective_from' => $data['effective_from'] ?? '2020-01-01',
                    'rate' => $data['rate'] ?? 0,
                    'is_active' => true,
                ]);
            } catch (\Throwable $e) {
                if (Schema::hasTable('tax_rates')) {
                    DB::table('tax_rates')->insert([
                        'tax_id' => $tax->id,
                        'effective_from' => $data['effective_from'] ?? '2020-01-01',
                        'rate' => $data['rate'] ?? 0,
                        'is_active' => true,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }

            return back()->with('success', 'Taxe créée.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Erreur : ' . substr($e->getMessage(), 0, 120));
        }
    }

    public function updateTax(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:vat,withholding,income_tax,ts,other',
            'is_active' => 'nullable|boolean',
        ]);
        $tax->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'is_active' => $data['is_active'] ?? true,
        ]);
        return back()->with('success', 'Taxe modifiée.');
    }

    public function destroyTax(Request $request, Tax $tax)
    {
        $tax->delete();
        return back()->with('success', 'Taxe supprimée.');
    }

    public function storeRate(Request $request, Tax $tax)
    {
        $data = $request->validate([
            'rate' => 'required|numeric|min:0|max:100',
            'effective_from' => 'required|date',
        ]);
        try {
            $tax->rates()->create($data + ['is_active' => true]);
        } catch (\Throwable $e) {
            if (Schema::hasTable('tax_rates')) {
                DB::table('tax_rates')->insert($data + ['tax_id' => $tax->id, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
        return back()->with('success', 'Nouveau taux ajouté (versionné avec date d\'effet).');
    }

    // ═══════════ DÉCLARATIONS ═══════════
    protected function sumAccountClass(Company $company, string $class, $start, $end): float
    {
        foreach (['journal_items', 'journal_entry_lines'] as $linesTable) {
            if (!Schema::hasTable($linesTable) || !Schema::hasTable('journal_entries')) continue;
            $cols = Schema::getColumnListing($linesTable);
            $fk = in_array('entry_id', $cols) ? 'entry_id' : (in_array('journal_entry_id', $cols) ? 'journal_entry_id' : null);
            if (!$fk || !in_array('account_number', $cols)) continue;
            try {
                return (float) DB::table($linesTable)
                    ->join('journal_entries', $linesTable . '.' . $fk, '=', 'journal_entries.id')
                    ->where('journal_entries.company_id', $company->id)
                    ->whereBetween('journal_entries.entry_date', [$start->toDateString(), $end->toDateString()])
                    ->where($linesTable . '.account_number', 'like', $class . '%')
                    ->sum(DB::raw($linesTable . '.credit - ' . $linesTable . '.debit'));
            } catch (\Throwable $e) {}
        }
        try {
            return (float) DB::table('sales_invoices')->where('company_id', $company->id)
                ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
                ->sum('total_ht');
        } catch (\Throwable $e) { return 0; }
    }

    public function generate(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $table = $this->declarationsTable();
        if (!$table) return back()->with('error', 'Table des déclarations introuvable.');

        $start = \Carbon\Carbon::create($data['year'], $data['month'], 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $revenue = $this->sumAccountClass($company, '7', $start, $end);
        $payroll = 0;
        try {
            $payroll = (float) DB::table('payslips')->where('company_id', $company->id)
                ->whereBetween('period_start', [$start->toDateString(), $end->toDateString()])
                ->sum('gross_salary');
        } catch (\Throwable $e) {}

        $today = now()->toDateString();
        $created = 0;

        foreach (Tax::where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $company->id))->where('is_active', true)->get() as $tax) {
            $rate = null;
            try {
                $rate = $tax->rates()->where('effective_from', '<=', $today)->where('is_active', true)->orderByDesc('effective_from')->first();
            } catch (\Throwable $e) {}
            $rateValue = $rate ? (float) $rate->rate : 0;

            $base = 0;
            if ($tax->type === 'vat') $base = $revenue;
            elseif (in_array($tax->type, ['ts', 'payroll']) || str_contains(strtoupper((string) $tax->code), 'TS')) $base = $payroll;
            else continue;

            $amount = round($base * $rateValue / 100);

            // Idempotence : ne pas recréer si déjà générée
            try {
                $cols = Schema::getColumnListing($table);
                $q = DB::table($table)->where('company_id', $company->id);
                if (in_array('year', $cols)) $q->where('year', $data['year']);
                if (in_array('month', $cols)) $q->where('month', $data['month']);
                if (in_array('tax_id', $cols)) $q->where('tax_id', $tax->id);
                if ($q->exists()) continue;
            } catch (\Throwable $e) { continue; }

            $row = [
                'company_id' => $company->id,
                'tax_id' => $tax->id,
                'tax_name' => $tax->name,
                'name' => $tax->name . ' ' . $start->format('m/Y'),
                'type' => $tax->type,
                'year' => $data['year'],
                'month' => $data['month'],
                'period_start' => $start->toDateString(),
                'period_end' => $end->toDateString(),
                'base_amount' => $base,
                'taxable_amount' => $base,
                'tax_amount' => $amount,
                'vat_amount' => $amount,
                'total_vat' => $amount,
                'vat_due' => $amount,
                'status' => 'pending',
            ];

            $cols = Schema::getColumnListing($table);
            $insert = array_intersect_key($row, array_flip($cols));
            $insert['created_at'] = now();
            $insert['updated_at'] = now();

            try { DB::table($table)->insert($insert); $created++; } catch (\Throwable $e) {}
        }

        return back()->with('success', $created > 0
            ? "{$created} déclaration(s) générée(s) pour " . $start->format('m/Y') . '.'
            : 'Aucune nouvelle déclaration (déjà générées ou aucune base imposable).');
    }

    public function updateStatus(Request $request, int $id)
    {
        $company = $this->company($request);
        $data = $request->validate(['status' => 'required|in:pending,filed,paid,late']);

        $table = $this->declarationsTable();
        if (!$table) return back()->with('error', 'Table introuvable.');

        try {
            $update = ['status' => $data['status'], 'updated_at' => now()];
            if (in_array($data['status'], ['filed', 'paid'])) $update['filed_at'] = now();
            DB::table($table)->where('id', $id)->where('company_id', $company->id)->update($update);
        } catch (\Throwable $e) {
            try {
                DB::table($table)->where('id', $id)->where('company_id', $company->id)->update(['status' => $data['status']]);
            } catch (\Throwable $e2) {}
        }

        return back()->with('success', 'Statut de la déclaration mis à jour.');
    }

    // ═══════════ ÉCHÉANCES ═══════════
    public function storeDeadline(Request $request)
    {
        $company = $this->company($request);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'deadline_date' => 'required|date',
        ]);

        if (Schema::hasTable('fiscal_deadlines')) {
            $cols = Schema::getColumnListing('fiscal_deadlines');
            $row = array_intersect_key([
                'company_id' => $company->id,
                'name' => $data['name'],
                'deadline_date' => $data['deadline_date'],
                'due_date' => $data['deadline_date'],
                'status' => 'pending',
            ], array_flip($cols));
            $row['created_at'] = now();
            $row['updated_at'] = now();
            try { DB::table('fiscal_deadlines')->insert($row); } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Échéance ajoutée.');
    }

    public function updateDeadline(Request $request, int $id)
    {
        $company = $this->company($request);
        $data = $request->validate(['status' => 'required|in:pending,done']);

        if (Schema::hasTable('fiscal_deadlines')) {
            try {
                DB::table('fiscal_deadlines')->where('id', $id)->where('company_id', $company->id)
                    ->update(['status' => $data['status'], 'updated_at' => now()]);
            } catch (\Throwable $e) {}
        }

        return back()->with('success', 'Échéance mise à jour.');
    }
}
