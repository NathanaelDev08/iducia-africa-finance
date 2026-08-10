<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinalTestDataSeeder extends Seeder
{
    protected Company $company;

    public function run(): void
    {
        echo "\n" . str_repeat('=', 62) . "\n";
        echo "   DONNÉES FINALES (schéma exact)\n";
        echo str_repeat('=', 62) . "\n";

        foreach (Company::all() as $company) {
            $this->company = $company;
            echo "\n>>> {$company->name}\n" . str_repeat('-', 62) . "\n";
            $this->seedContracts();
            $this->seedPayroll();
            $this->seedClients();
            $this->seedSalesInvoices();
            $this->seedSuppliers();
            $this->seedPurchaseInvoices();
        }

        echo "\n" . str_repeat('=', 62) . "\n";
        $this->summary();
    }

    protected function hasTable(string $t): bool
    {
        try { return Schema::hasTable($t); } catch (\Throwable $e) { return false; }
    }

    protected function ins(string $table, array $data, string $label = ''): ?int
    {
        try {
            $cols = Schema::getColumnListing($table);
            $row = array_intersect_key($data, array_flip($cols));
            if (in_array('company_id', $cols) && !isset($row['company_id'])) {
                $row['company_id'] = $this->company->id;
            }
            if (in_array('created_at', $cols)) $row['created_at'] = now();
            if (in_array('updated_at', $cols)) $row['updated_at'] = now();
            return DB::table($table)->insertGetId($row);
        } catch (\Throwable $e) {
            echo "  ✗ {$table} {$label} : " . substr($e->getMessage(), 0, 150) . "\n";
            return null;
        }
    }

    protected function defaultSalary(string $matricule): float
    {
        $map = [
            'EMP-001' => 1500000, 'EMP-002' => 650000, 'EMP-003' => 700000, 'EMP-004' => 550000,
            'EMP-005' => 600000, 'EMP-006' => 350000, 'EMP-007' => 500000, 'EMP-008' => 480000,
        ];
        return $map[$matricule] ?? 450000;
    }

    // ═══════════ 1. CONTRATS (salaire de chaque employé) ═══════════
    protected function seedContracts(): void
    {
        if (!$this->hasTable('employee_contracts')) { echo "⚠ employee_contracts absente\n"; return; }

        $contractTypeId = null;
        if ($this->hasTable('contract_types')) {
            try {
                $ct = DB::table('contract_types')->where('company_id', $this->company->id)->first()
                    ?? DB::table('contract_types')->first();
                $contractTypeId = $ct->id ?? null;
            } catch (\Throwable $e) {}
        }

        $employees = DB::table('employees')->where('company_id', $this->company->id)->whereNull('deleted_at')->get();
        $created = 0; $updated = 0;

        foreach ($employees as $emp) {
            $salary = $this->defaultSalary($emp->matricule ?? '');

            try {
                $existing = DB::table('employee_contracts')->where('employee_id', $emp->id)->first();
            } catch (\Throwable $e) { $existing = null; }

            if ($existing) {
                if ((float) ($existing->base_salary ?? 0) <= 0) {
                    try {
                        DB::table('employee_contracts')->where('id', $existing->id)
                            ->update(['base_salary' => $salary, 'status' => 'active', 'updated_at' => now()]);
                        $updated++;
                    } catch (\Throwable $e) {}
                }
                continue;
            }

            $data = [
                'company_id' => $this->company->id,
                'employee_id' => $emp->id,
                'contract_number' => 'CTR-' . ($emp->matricule ?? $emp->id),
                'start_date' => $emp->hire_date ?? now()->toDateString(),
                'working_hours_per_week' => 40,
                'base_salary' => $salary,
                'status' => 'active',
            ];
            if ($contractTypeId) $data['contract_type_id'] = $contractTypeId;

            if ($this->ins('employee_contracts', $data, $emp->matricule ?? '')) $created++;
        }
        echo "📝 Contrats : {$created} créés, {$updated} complétés\n";
    }

    // ═══════════ 2. PAIE (période + bulletins + rubriques) ═══════════
    protected function seedPayroll(): void
    {
        if (!$this->hasTable('pay_runs') || !$this->hasTable('payslips')) { echo "⚠ tables paie absentes\n"; return; }

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $reference = 'PAIE-' . now()->format('Y-m');

        try {
            $run = DB::table('pay_runs')->where('company_id', $this->company->id)->where('reference', $reference)->first();
        } catch (\Throwable $e) { $run = null; }

        if (!$run) {
            $runId = $this->ins('pay_runs', [
                'company_id' => $this->company->id,
                'name' => 'Paie ' . now()->format('m/Y'),
                'reference' => $reference,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'payment_date' => $periodEnd->toDateString(),
                'status' => 'calculated',
                'is_locked' => false,
            ]);
            if (!$runId) { echo "⚠ Période de paie impossible\n"; return; }
        } else {
            $runId = $run->id;
        }

        $employees = DB::table('employees')->where('company_id', $this->company->id)->whereNull('deleted_at')->get();
        $created = 0; $n = 0;

        foreach ($employees as $emp) {
            // Salaire depuis le contrat actif
            $base = 0;
            try {
                $contract = DB::table('employee_contracts')->where('employee_id', $emp->id)->where('status', 'active')->first()
                    ?? DB::table('employee_contracts')->where('employee_id', $emp->id)->first();
                $base = (float) ($contract->base_salary ?? 0);
            } catch (\Throwable $e) {}
            if ($base <= 0) $base = $this->defaultSalary($emp->matricule ?? '');

            try {
                if (DB::table('payslips')->where('pay_run_id', $runId)->where('employee_id', $emp->id)->exists()) continue;
            } catch (\Throwable $e) {}

            $n++;

            // ── Calculs de paie ──
            $anciennete = round($base * 0.05);
            $transport = 60000;
            $gross = $base + $anciennete + $transport;
            $cnpsBase = min($gross, 1200000);
            $cnpsSal = round($cnpsBase * 0.0425);          // part salariale
            $taxable = max(0, $gross - $cnpsSal);
            $ir = round($taxable * 0.05);                   // impôt
            $cnpsPat = round($cnpsBase * 0.077);            // part patronale
            $at = round($cnpsBase * 0.02);
            $pf = round($gross * 0.0575);
            $deductions = $cnpsSal + $ir;
            $net = $gross - $deductions;
            $employer = $cnpsPat + $at + $pf;

            $payslipId = $this->ins('payslips', [
                'company_id' => $this->company->id,
                'pay_run_id' => $runId,
                'employee_id' => $emp->id,
                'slip_number' => 'BUL-' . now()->format('Ym') . '-' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                'base_salary' => $base,
                'gross_salary' => $gross,
                'total_earnings' => $gross,
                'total_deductions' => $deductions,
                'total_employee_contributions' => $cnpsSal,
                'total_employer_contributions' => $employer,
                'employer_contributions' => $employer,
                'taxable_income' => $taxable,
                'income_tax' => $ir,
                'net_salary' => $net,
                'status' => 'calculated',
            ], $emp->matricule ?? '');

            if (!$payslipId) continue;

            // Rubriques (colonnes exactes : name, type, base_amount, rate, amount, is_earning, display_order)
            if ($this->hasTable('payslip_items')) {
                $items = [
                    ['name' => 'Salaire de base', 'type' => 'earning', 'base_amount' => $base, 'rate' => 100, 'amount' => $base, 'is_earning' => true, 'display_order' => 1],
                    ['name' => "Prime d'ancienneté (5%)", 'type' => 'earning', 'base_amount' => $base, 'rate' => 5, 'amount' => $anciennete, 'is_earning' => true, 'display_order' => 2],
                    ['name' => 'Prime de transport', 'type' => 'earning', 'base_amount' => $transport, 'rate' => 0, 'amount' => $transport, 'is_earning' => true, 'display_order' => 3],
                    ['name' => 'CNPS Retraite (part salariale)', 'type' => 'employee_contribution', 'base_amount' => $cnpsBase, 'rate' => 4.25, 'amount' => $cnpsSal, 'is_earning' => false, 'display_order' => 10],
                    ['name' => 'Impôt sur salaire (IR)', 'type' => 'deduction', 'base_amount' => $taxable, 'rate' => 5, 'amount' => $ir, 'is_earning' => false, 'display_order' => 11],
                    ['name' => 'CNPS Retraite (part patronale)', 'type' => 'employer_contribution', 'base_amount' => $cnpsBase, 'rate' => 7.7, 'amount' => $cnpsPat, 'is_earning' => false, 'display_order' => 20],
                    ['name' => 'Accidents du travail', 'type' => 'employer_contribution', 'base_amount' => $cnpsBase, 'rate' => 2, 'amount' => $at, 'is_earning' => false, 'display_order' => 21],
                    ['name' => 'Prestations familiales', 'type' => 'employer_contribution', 'base_amount' => $gross, 'rate' => 5.75, 'amount' => $pf, 'is_earning' => false, 'display_order' => 22],
                ];
                foreach ($items as $item) {
                    $this->ins('payslip_items', array_merge($item, ['payslip_id' => $payslipId]));
                }
            }
            $created++;
        }

        echo "📅 Période : {$reference} | 📄 Bulletins : {$created}\n";
    }

    // ═══════════ 3. CLIENTS ═══════════
    protected function seedClients(): void
    {
        if (!$this->hasTable('clients')) { echo "⚠ clients absente\n"; return; }

        $clients = [
            ['code' => 'CLI-001', 'name' => 'SOCIÉTÉ IVOIRIENNE DE DISTRIBUTION', 'contact_name' => 'K. Séka', 'email' => 'contact@sid-ci.com', 'phone' => '+225 27 20 21 22 23', 'tax_number' => 'CI-2019-A-1234', 'account_number' => '411100', 'payment_terms' => 30],
            ['code' => 'CLI-002', 'name' => 'GROUPE ATLANTIQUE CI', 'contact_name' => 'M. Dosso', 'email' => 'info@atlantique.ci', 'phone' => '+225 27 21 30 40 50', 'tax_number' => 'CI-2015-B-5678', 'account_number' => '411100', 'payment_terms' => 45],
            ['code' => 'CLI-003', 'name' => 'PHARMACIE DU PLATEAU', 'contact_name' => 'Dr. Assi', 'email' => 'pharma.plateau@gmail.com', 'phone' => '+225 27 20 31 41 51', 'tax_number' => 'CI-2021-C-9012', 'account_number' => '411100', 'payment_terms' => 30],
            ['code' => 'CLI-004', 'name' => 'TRANSPORTS BOUAKÉ SARL', 'contact_name' => 'A. Kone', 'email' => 'transports.bouake@yahoo.fr', 'phone' => '+225 25 60 11 22 33', 'tax_number' => 'CI-2018-D-3456', 'account_number' => '411100', 'payment_terms' => 60],
        ];

        $created = 0;
        foreach ($clients as $cl) {
            try {
                if (DB::table('clients')->where('company_id', $this->company->id)->where('code', $cl['code'])->exists()) continue;
            } catch (\Throwable $e) {}
            if ($this->ins('clients', array_merge($cl, ['is_active' => true]))) $created++;
        }
        echo "🤝 Clients : {$created}\n";
    }

    // ═══════════ 4. FACTURES DE VENTE ═══════════
    protected function seedSalesInvoices(): void
    {
        if (!$this->hasTable('sales_invoices')) { echo "⚠ sales_invoices absente\n"; return; }

        $clients = collect();
        try { $clients = DB::table('clients')->where('company_id', $this->company->id)->get(); } catch (\Throwable $e) {}
        if ($clients->isEmpty()) { echo "⚠ Aucun client\n"; return; }

        $year = now()->format('Y');
        $invoices = [
            ['reference' => "FAC-{$year}-0001", 'client' => 0, 'ht' => 2500000, 'days' => 75, 'status' => 'paid'],
            ['reference' => "FAC-{$year}-0002", 'client' => 1, 'ht' => 1800000, 'days' => 60, 'status' => 'paid'],
            ['reference' => "FAC-{$year}-0003", 'client' => 2, 'ht' => 950000, 'days' => 40, 'status' => 'pending'],
            ['reference' => "FAC-{$year}-0004", 'client' => 0, 'ht' => 3200000, 'days' => 20, 'status' => 'pending'],
            ['reference' => "FAC-{$year}-0005", 'client' => 3, 'ht' => 1450000, 'days' => 5, 'status' => 'pending'],
        ];

        $created = 0;
        foreach ($invoices as $inv) {
            try {
                if (DB::table('sales_invoices')->where('company_id', $this->company->id)->where('reference', $inv['reference'])->exists()) continue;
            } catch (\Throwable $e) {}

            $client = $clients[$inv['client']] ?? $clients[0];
            $tax = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $tax;
            $date = now()->subDays($inv['days']);

            $id = $this->ins('sales_invoices', [
                'client_id' => $client->id,
                'reference' => $inv['reference'],
                'invoice_date' => $date->toDateString(),
                'due_date' => $date->copy()->addDays(30)->toDateString(),
                'status' => $inv['status'],
                'total_ht' => $inv['ht'],
                'total_tax' => $tax,
                'total_ttc' => $ttc,
                'amount_paid' => $inv['status'] === 'paid' ? $ttc : 0,
            ], $inv['reference']);
            if ($id) $created++;
        }
        echo "🧾 Factures de vente : {$created}\n";
    }

    // ═══════════ 5. FOURNISSEURS ═══════════
    protected function seedSuppliers(): void
    {
        if (!$this->hasTable('suppliers')) { echo "⚠ suppliers absente\n"; return; }

        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'FOURNITURES BUREAU PLUS', 'contact_name' => 'S. Brou', 'email' => 'contact@fbplus.ci', 'phone' => '+225 27 22 44 55 66', 'tax_number' => 'CI-2016-E-7890', 'account_number' => '401100'],
            ['code' => 'SUP-002', 'name' => 'CFAO TECHNOLOGY CI', 'contact_name' => 'H. Niamkey', 'email' => 'ventes@cfao.ci', 'phone' => '+225 27 21 77 88 99', 'tax_number' => 'CI-2010-F-2345', 'account_number' => '401100'],
            ['code' => 'SUP-003', 'name' => 'IMPRIMERIE VIE NOUVELLE', 'contact_name' => 'P. Aka', 'email' => 'ivn@imprimerie.ci', 'phone' => '+225 27 20 12 34 56', 'tax_number' => 'CI-2019-G-6789', 'account_number' => '401100'],
        ];

        $created = 0;
        foreach ($suppliers as $s) {
            try {
                if (DB::table('suppliers')->where('company_id', $this->company->id)->where('code', $s['code'])->exists()) continue;
            } catch (\Throwable $e) {}
            if ($this->ins('suppliers', $s)) $created++;
        }
        echo "🏭 Fournisseurs : {$created}\n";
    }

    // ═══════════ 6. FACTURES D'ACHAT ═══════════
    protected function seedPurchaseInvoices(): void
    {
        if (!$this->hasTable('purchase_invoices')) { echo "⚠ purchase_invoices absente\n"; return; }

        $suppliers = collect();
        try { $suppliers = DB::table('suppliers')->where('company_id', $this->company->id)->get(); } catch (\Throwable $e) {}
        if ($suppliers->isEmpty()) { echo "⚠ Aucun fournisseur\n"; return; }

        $year = now()->format('Y');
        $invoices = [
            ['reference' => "FAF-{$year}-0001", 'supplier' => 0, 'ht' => 850000, 'days' => 55, 'status' => 'paid'],
            ['reference' => "FAF-{$year}-0002", 'supplier' => 1, 'ht' => 1200000, 'days' => 30, 'status' => 'pending'],
            ['reference' => "FAF-{$year}-0003", 'supplier' => 2, 'ht' => 450000, 'days' => 10, 'status' => 'pending'],
        ];

        $created = 0;
        foreach ($invoices as $inv) {
            try {
                if (DB::table('purchase_invoices')->where('company_id', $this->company->id)->where('reference', $inv['reference'])->exists()) continue;
            } catch (\Throwable $e) {}

            $supplier = $suppliers[$inv['supplier']] ?? $suppliers[0];
            $tax = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $tax;
            $date = now()->subDays($inv['days']);

            $id = $this->ins('purchase_invoices', [
                'supplier_id' => $supplier->id,
                'reference' => $inv['reference'],
                'invoice_date' => $date->toDateString(),
                'issue_date' => $date->toDateString(),
                'due_date' => $date->copy()->addDays(30)->toDateString(),
                'status' => $inv['status'],
                'total_ht' => $inv['ht'],
                'total_tax' => $tax,
                'total_vat' => $tax,
                'total_ttc' => $ttc,
                'amount_paid' => $inv['status'] === 'paid' ? $ttc : 0,
            ], $inv['reference']);
            if ($id) $created++;
        }
        echo "📥 Factures d'achat : {$created}\n";
    }

    // ═══════════ RÉCAPITULATIF ═══════════
    protected function summary(): void
    {
        echo "📊 RÉCAPITULATIF FINAL :\n\n";
        foreach (['employee_contracts', 'pay_runs', 'payslips', 'payslip_items', 'clients', 'sales_invoices', 'suppliers', 'purchase_invoices'] as $t) {
            if ($this->hasTable($t)) {
                try {
                    $count = DB::table($t)->count();
                    printf("   %-22s %4d\n", $t, $count);
                } catch (\Throwable $e) {}
            }
        }
        echo "\n✅ TERMINÉ !\n";
    }
}
