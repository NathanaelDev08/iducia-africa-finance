<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairTestDataSeeder extends Seeder
{
    protected Company $company;
    protected array $colCache = [];

    public function run(): void
    {
        foreach (Company::all() as $company) {
            $this->company = $company;
            echo "\n>>> ENTREPRISE : {$company->name}\n";
            echo str_repeat('-', 60) . "\n";
            try { $this->repairPayroll(); } catch (\Throwable $e) { echo "⚠ Paie : " . substr($e->getMessage(), 0, 150) . "\n"; }
            try { $this->repairSales(); } catch (\Throwable $e) { echo "⚠ Ventes : " . substr($e->getMessage(), 0, 150) . "\n"; }
            try { $this->repairPurchases(); } catch (\Throwable $e) { echo "⚠ Achats : " . substr($e->getMessage(), 0, 150) . "\n"; }
        }
        echo "\n✅ RÉPARATION TERMINÉE\n";
    }

    protected function cols(string $table): array
    {
        if (!isset($this->colCache[$table])) {
            try { $this->colCache[$table] = Schema::getColumnListing($table); }
            catch (\Throwable $e) { $this->colCache[$table] = []; }
        }
        return $this->colCache[$table];
    }

    protected function hasTable(string $t): bool
    {
        try { return Schema::hasTable($t); } catch (\Throwable $e) { return false; }
    }

    protected function insertVerbose(string $table, array $data): ?int
    {
        if (!$this->hasTable($table)) { echo "  ✗ table {$table} absente\n"; return null; }
        $cols = $this->cols($table);
        $row = array_intersect_key($data, array_flip($cols));
        if (in_array('company_id', $cols) && !isset($row['company_id'])) {
            $row['company_id'] = $this->company->id;
        }
        if (in_array('created_at', $cols)) $row['created_at'] = now();
        if (in_array('updated_at', $cols)) $row['updated_at'] = now();
        try {
            return DB::table($table)->insertGetId($row);
        } catch (\Throwable $e) {
            echo "  ✗ ERREUR [{$table}] : " . substr($e->getMessage(), 0, 180) . "\n";
            return null;
        }
    }

    // Récupère le salaire : employees → contracts → défaut
    protected function getSalary($emp): float
    {
        foreach (['base_salary', 'salary', 'monthly_salary', 'gross_salary'] as $col) {
            if (isset($emp->$col) && (float) $emp->$col > 0) return (float) $emp->$col;
        }

        foreach (['contracts', 'employee_contracts'] as $table) {
            if (!$this->hasTable($table)) continue;
            try {
                $contract = DB::table($table)->where('employee_id', $emp->id)->first();
                if ($contract) {
                    foreach (['base_salary', 'salary', 'monthly_salary', 'gross_salary'] as $col) {
                        if (isset($contract->$col) && (float) $contract->$col > 0) return (float) $contract->$col;
                    }
                }
            } catch (\Throwable $e) {}
        }

        $defaults = [
            'EMP-001' => 1500000, 'EMP-002' => 650000, 'EMP-003' => 700000, 'EMP-004' => 550000,
            'EMP-005' => 600000, 'EMP-006' => 350000, 'EMP-007' => 500000, 'EMP-008' => 480000,
        ];
        return $defaults[$emp->matricule ?? ''] ?? 450000;
    }

    // ═══════════ RÉPARATION PAIE ═══════════
    protected function repairPayroll(): void
    {
        if (!$this->hasTable('pay_runs') || !$this->hasTable('payslips')) {
            echo "⚠ Tables pay_runs/payslips manquantes\n";
            return;
        }

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $reference = 'PAIE-' . now()->format('Y-m');

        try {
            $run = DB::table('pay_runs')->where('company_id', $this->company->id)->where('reference', $reference)->first();
        } catch (\Throwable $e) { $run = null; }

        if (!$run) {
            $runId = $this->insertVerbose('pay_runs', [
                'name' => 'Paie ' . now()->format('F Y'),
                'reference' => $reference,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'payment_date' => $periodEnd->toDateString(),
                'status' => 'calculated',
            ]);
            if (!$runId) { echo "⚠ Période de paie impossible\n"; return; }
            echo "✓ Période créée\n";
        } else {
            $runId = $run->id;
        }

        $employees = DB::table('employees')->where('company_id', $this->company->id)->get();
        $created = 0;

        foreach ($employees as $emp) {
            $base = $this->getSalary($emp);

            try {
                if (DB::table('payslips')->where('pay_run_id', $runId)->where('employee_id', $emp->id)->exists()) continue;
            } catch (\Throwable $e) {}

            $anciennete = round($base * 0.05);
            $transport = 60000;
            $gross = $base + $anciennete + $transport;
            $cnpsBase = min($gross, 1200000);
            $cnpsSal = round($cnpsBase * 0.0425);
            $cnpsPat = round($cnpsBase * 0.077);
            $at = round($cnpsBase * 0.02);
            $pf = round($gross * 0.0575);
            $ir = round(($gross - $cnpsSal) * 0.05);
            $deductions = $cnpsSal + $ir;
            $net = $gross - $deductions;
            $employer = $cnpsPat + $at + $pf;

            $payslipId = $this->insertVerbose('payslips', [
                'pay_run_id' => $runId,
                'employee_id' => $emp->id,
                'status' => 'calculated',
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'base_salary' => $base,
                'gross_salary' => $gross,
                'total_earnings' => $gross,
                'total_deductions' => $deductions,
                'net_salary' => $net,
                'employer_contributions' => $employer,
            ]);

            if (!$payslipId) continue;

            if ($this->hasTable('payslip_items')) {
                $items = [
                    ['code' => 'SAL_BASE', 'label' => 'Salaire de base', 'type' => 'earning', 'base' => $base, 'rate' => 100, 'amount' => $base, 'sort_order' => 1],
                    ['code' => 'PRIME_ANC', 'label' => "Prime d'ancienneté (5%)", 'type' => 'earning', 'base' => $base, 'rate' => 5, 'amount' => $anciennete, 'sort_order' => 2],
                    ['code' => 'PRIME_TRANS', 'label' => 'Prime de transport', 'type' => 'earning', 'base' => $transport, 'rate' => 0, 'amount' => $transport, 'sort_order' => 3],
                    ['code' => 'CNPS_RP', 'label' => 'CNPS Retraite (part salariale)', 'type' => 'employee_contribution', 'base' => $cnpsBase, 'rate' => 4.25, 'amount' => $cnpsSal, 'sort_order' => 10],
                    ['code' => 'IR_ITS', 'label' => 'Impôt sur salaire', 'type' => 'deduction', 'base' => $gross - $cnpsSal, 'rate' => 5, 'amount' => $ir, 'sort_order' => 11],
                    ['code' => 'CNPS_PAT', 'label' => 'CNPS Retraite (part patronale)', 'type' => 'employer_contribution', 'base' => $cnpsBase, 'rate' => 7.7, 'amount' => $cnpsPat, 'sort_order' => 20],
                    ['code' => 'CNPS_AT', 'label' => 'Accidents du travail', 'type' => 'employer_contribution', 'base' => $cnpsBase, 'rate' => 2, 'amount' => $at, 'sort_order' => 21],
                    ['code' => 'CNPS_PF', 'label' => 'Prestations familiales', 'type' => 'employer_contribution', 'base' => $gross, 'rate' => 5.75, 'amount' => $pf, 'sort_order' => 22],
                ];
                foreach ($items as $item) {
                    $this->insertVerbose('payslip_items', array_merge($item, ['payslip_id' => $payslipId]));
                }
            }
            $created++;
        }
        echo "✓ Bulletins créés : {$created}\n";
    }

    // ═══════════ RÉPARATION VENTES ═══════════
    protected function repairSales(): void
    {
        if (!$this->hasTable('sales_invoices')) { echo "⚠ Table sales_invoices absente\n"; return; }

        if ($this->hasTable('clients')) {
            $clientsData = [
                ['code' => 'CLI-001', 'name' => 'SOCIÉTÉ IVOIRIENNE DE DISTRIBUTION', 'email' => 'contact@sid-ci.com'],
                ['code' => 'CLI-002', 'name' => 'GROUPE ATLANTIQUE CI', 'email' => 'info@atlantique.ci'],
                ['code' => 'CLI-003', 'name' => 'PHARMACIE DU PLATEAU', 'email' => 'pharma.plateau@gmail.com'],
                ['code' => 'CLI-004', 'name' => 'TRANSPORTS BOUAKÉ SARL', 'email' => 'transports.bouake@yahoo.fr'],
            ];
            foreach ($clientsData as $cl) {
                try {
                    if (DB::table('clients')->where('company_id', $this->company->id)->where('code', $cl['code'])->exists()) continue;
                } catch (\Throwable $e) {}
                $this->insertVerbose('clients', $cl);
            }
        }

        $clients = collect();
        try { $clients = DB::table('clients')->where('company_id', $this->company->id)->get(); } catch (\Throwable $e) {}
        if ($clients->isEmpty()) { echo "⚠ Aucun client\n"; return; }

        $year = now()->format('Y');
        $invoices = [
            ['number' => "FAC-{$year}-0001", 'client' => 0, 'ht' => 2500000, 'days' => 75, 'status' => 'paid'],
            ['number' => "FAC-{$year}-0002", 'client' => 1, 'ht' => 1800000, 'days' => 60, 'status' => 'paid'],
            ['number' => "FAC-{$year}-0003", 'client' => 2, 'ht' => 950000, 'days' => 40, 'status' => 'pending'],
            ['number' => "FAC-{$year}-0004", 'client' => 0, 'ht' => 3200000, 'days' => 20, 'status' => 'pending'],
            ['number' => "FAC-{$year}-0005", 'client' => 3, 'ht' => 1450000, 'days' => 5, 'status' => 'pending'],
        ];

        $created = 0;
        foreach ($invoices as $inv) {
            try {
                $exists = false;
                foreach (['number', 'reference', 'invoice_number'] as $col) {
                    if (in_array($col, $this->cols('sales_invoices'))) {
                        if (DB::table('sales_invoices')->where($col, $inv['number'])->where('company_id', $this->company->id)->exists()) { $exists = true; break; }
                    }
                }
                if ($exists) continue;
            } catch (\Throwable $e) {}

            $client = $clients[$inv['client']] ?? $clients[0];
            $vat = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $vat;
            $date = now()->subDays($inv['days']);

            $id = $this->insertVerbose('sales_invoices', [
                'client_id' => $client->id,
                'number' => $inv['number'],
                'reference' => $inv['number'],
                'invoice_number' => $inv['number'],
                'issue_date' => $date->toDateString(),
                'invoice_date' => $date->toDateString(),
                'due_date' => $date->copy()->addDays(30)->toDateString(),
                'status' => $inv['status'],
                'total_ht' => $inv['ht'],
                'total_vat' => $vat,
                'total_ttc' => $ttc,
                'amount_ht' => $inv['ht'],
                'amount_vat' => $vat,
                'amount_ttc' => $ttc,
                'total' => $ttc,
                'currency' => 'XOF',
            ]);
            if ($id) $created++;
        }
        echo "✓ Factures vente : {$created}\n";
    }

    // ═══════════ RÉPARATION ACHATS ═══════════
    protected function repairPurchases(): void
    {
        if (!$this->hasTable('purchase_invoices')) { echo "⚠ Table purchase_invoices absente\n"; return; }

        if ($this->hasTable('suppliers')) {
            $supData = [
                ['code' => 'SUP-001', 'name' => 'FOURNITURES BUREAU PLUS', 'email' => 'contact@fbplus.ci'],
                ['code' => 'SUP-002', 'name' => 'CFAO TECHNOLOGY CI', 'email' => 'ventes@cfao.ci'],
                ['code' => 'SUP-003', 'name' => 'IMPRIMERIE VIE NOUVELLE', 'email' => 'ivn@imprimerie.ci'],
            ];
            foreach ($supData as $s) {
                try {
                    if (DB::table('suppliers')->where('company_id', $this->company->id)->where('code', $s['code'])->exists()) continue;
                } catch (\Throwable $e) {}
                $this->insertVerbose('suppliers', $s);
            }
        }

        $suppliers = collect();
        try { $suppliers = DB::table('suppliers')->where('company_id', $this->company->id)->get(); } catch (\Throwable $e) {}
        if ($suppliers->isEmpty()) { echo "⚠ Aucun fournisseur\n"; return; }

        $year = now()->format('Y');
        $invoices = [
            ['number' => "FAF-{$year}-0001", 'supplier' => 0, 'ht' => 850000, 'days' => 55, 'status' => 'paid'],
            ['number' => "FAF-{$year}-0002", 'supplier' => 1, 'ht' => 1200000, 'days' => 30, 'status' => 'pending'],
            ['number' => "FAF-{$year}-0003", 'supplier' => 2, 'ht' => 450000, 'days' => 10, 'status' => 'pending'],
        ];

        $created = 0;
        foreach ($invoices as $inv) {
            try {
                $exists = false;
                foreach (['number', 'reference', 'invoice_number'] as $col) {
                    if (in_array($col, $this->cols('purchase_invoices'))) {
                        if (DB::table('purchase_invoices')->where($col, $inv['number'])->where('company_id', $this->company->id)->exists()) { $exists = true; break; }
                    }
                }
                if ($exists) continue;
            } catch (\Throwable $e) {}

            $supplier = $suppliers[$inv['supplier']] ?? $suppliers[0];
            $vat = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $vat;
            $date = now()->subDays($inv['days']);

            $id = $this->insertVerbose('purchase_invoices', [
                'supplier_id' => $supplier->id,
                'number' => $inv['number'],
                'reference' => $inv['number'],
                'invoice_number' => $inv['number'],
                'issue_date' => $date->toDateString(),
                'invoice_date' => $date->toDateString(),
                'due_date' => $date->copy()->addDays(30)->toDateString(),
                'status' => $inv['status'],
                'total_ht' => $inv['ht'],
                'total_vat' => $vat,
                'total_ttc' => $ttc,
                'amount_ht' => $inv['ht'],
                'amount_vat' => $vat,
                'amount_ttc' => $ttc,
                'total' => $ttc,
            ]);
            if ($id) $created++;
        }
        echo "✓ Factures achat : {$created}\n";
    }
}
