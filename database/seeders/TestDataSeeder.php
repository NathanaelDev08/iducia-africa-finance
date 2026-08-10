<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TestDataSeeder extends Seeder
{
    protected Company $company;
    protected array $colCache = [];
    protected array $deptIds = [];
    protected array $posIds = [];

    public function run(): void
    {
        echo "\n";
        echo str_repeat('=', 62) . "\n";
        echo "   DONNÉES DE TEST — SYSTÈME COMPLET\n";
        echo str_repeat('=', 62) . "\n";

        $this->seedUsers();

        foreach (Company::all() as $i => $company) {
            $this->company = $company;
            $this->deptIds = [];
            $this->posIds = [];

            echo "\n>>> ENTREPRISE : {$company->name}\n";
            echo str_repeat('-', 62) . "\n";

            try { $this->seedAccounts(); } catch (\Throwable $e) { echo "⚠ Plan comptable ignoré\n"; }
            try { $this->seedJournals(); } catch (\Throwable $e) { echo "⚠ Journaux ignorés\n"; }
            try { $this->seedDepartments(); } catch (\Throwable $e) { echo "⚠ Départements ignorés\n"; }
            try { $this->seedPositions(); } catch (\Throwable $e) { echo "⚠ Postes ignorés\n"; }
            try { $this->seedEmployees($i); } catch (\Throwable $e) { echo "⚠ Employés ignorés\n"; }
            try { $this->seedContracts(); } catch (\Throwable $e) { echo "⚠ Contrats ignorés\n"; }
            try { $this->seedPayItems(); } catch (\Throwable $e) { echo "⚠ Rubriques paie ignorées\n"; }
            try { $this->seedContributions(); } catch (\Throwable $e) { echo "⚠ Cotisations ignorées\n"; }
            try { $this->seedPayroll(); } catch (\Throwable $e) { echo "⚠ Paie ignorée\n"; }
            try { $this->seedClients($i); } catch (\Throwable $e) { echo "⚠ Clients ignorés\n"; }
            try { $this->seedSalesInvoices(); } catch (\Throwable $e) { echo "⚠ Factures vente ignorées\n"; }
            try { $this->seedSuppliers($i); } catch (\Throwable $e) { echo "⚠ Fournisseurs ignorés\n"; }
            try { $this->seedPurchaseInvoices(); } catch (\Throwable $e) { echo "⚠ Factures achat ignorées\n"; }
            try { $this->seedAssets(); } catch (\Throwable $e) { echo "⚠ Immobilisations ignorées\n"; }
            try { $this->seedExchangeRates(); } catch (\Throwable $e) { echo "⚠ Devises ignorées\n"; }
            try { $this->seedTaxDeclarations(); } catch (\Throwable $e) { echo "⚠ Fiscalité ignorée\n"; }
            try { $this->seedJournalEntries(); } catch (\Throwable $e) { echo "⚠ Écritures ignorées\n"; }
        }

        echo "\n" . str_repeat('=', 62) . "\n";
        $this->printSummary();
    }

    // ═══════════════════ HELPERS ═══════════════════

    protected function hasTable(string $t): bool
    {
        try { return Schema::hasTable($t); } catch (\Throwable $e) { return false; }
    }

    protected function cols(string $table): array
    {
        if (!isset($this->colCache[$table])) {
            try { $this->colCache[$table] = Schema::getColumnListing($table); }
            catch (\Throwable $e) { $this->colCache[$table] = []; }
        }
        return $this->colCache[$table];
    }

    protected function insertRow(string $table, array $data): ?int
    {
        if (!$this->hasTable($table)) return null;
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
            return null;
        }
    }

    protected function existsIn(string $table, array $where): bool
    {
        try {
            if (!$this->hasTable($table)) return false;
            $cols = $this->cols($table);
            $q = DB::table($table);
            foreach ($where as $k => $v) {
                if (in_array($k, $cols)) $q->where($k, $v);
            }
            return $q->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ═══════════════════ UTILISATEURS ═══════════════════

    protected function seedUsers(): void
    {
        $users = [
            ['name' => 'Awa KONÉ', 'email' => 'comptable@fiducia-africa.com'],
            ['name' => 'Moussa TRAORÉ', 'email' => 'rh@fiducia-africa.com'],
            ['name' => 'Fatou DIABATÉ', 'email' => 'commercial@fiducia-africa.com'],
        ];
        $created = 0;
        foreach ($users as $data) {
            if (User::where('email', $data['email'])->exists()) continue;
            try {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]);
                foreach (Company::all() as $c) {
                    try { $user->companies()->syncWithoutDetaching([$c->id => ['role' => 'admin']]); }
                    catch (\Throwable $e) {
                        try { $user->companies()->syncWithoutDetaching([$c->id]); } catch (\Throwable $e2) {}
                    }
                }
                $created++;
            } catch (\Throwable $e) {}
        }
        echo "👥 Utilisateurs : {$created} créé(s) [mot de passe : password123]\n";
    }

    // ═══════════════════ PLAN COMPTABLE SYSCOHADA ═══════════════════

    protected function seedAccounts(): void
    {
        if (!$this->hasTable('accounts')) { echo "⚠ Table accounts absente\n"; return; }

        $accounts = [
            ['number' => '211100', 'name' => 'Immobilisations incorporelles', 'class_number' => 2, 'type' => 'asset'],
            ['number' => '213100', 'name' => 'Matériel et outillage', 'class_number' => 2, 'type' => 'asset'],
            ['number' => '218100', 'name' => 'Matériel de transport', 'class_number' => 2, 'type' => 'asset'],
            ['number' => '218300', 'name' => 'Matériel informatique', 'class_number' => 2, 'type' => 'asset'],
            ['number' => '281300', 'name' => 'Amortissements du matériel', 'class_number' => 2, 'type' => 'asset'],
            ['number' => '401100', 'name' => 'Fournisseurs', 'class_number' => 4, 'type' => 'liability'],
            ['number' => '411100', 'name' => 'Clients', 'class_number' => 4, 'type' => 'asset'],
            ['number' => '421100', 'name' => 'Personnel - Rémunérations dues', 'class_number' => 4, 'type' => 'liability'],
            ['number' => '431100', 'name' => 'CNPS - Cotisations à payer', 'class_number' => 4, 'type' => 'liability'],
            ['number' => '442100', 'name' => 'État - Impôts retenus sur salaires', 'class_number' => 4, 'type' => 'liability'],
            ['number' => '443100', 'name' => 'TVA facturée', 'class_number' => 4, 'type' => 'liability'],
            ['number' => '445100', 'name' => 'TVA déductible', 'class_number' => 4, 'type' => 'asset'],
            ['number' => '521100', 'name' => 'Banque SGBCI - Compte principal', 'class_number' => 5, 'type' => 'bank'],
            ['number' => '521200', 'name' => 'Banque BICICI - Compte secondaire', 'class_number' => 5, 'type' => 'bank'],
            ['number' => '571100', 'name' => 'Caisse', 'class_number' => 5, 'type' => 'bank'],
            ['number' => '601100', 'name' => 'Achats de matières premières', 'class_number' => 6, 'type' => 'expense'],
            ['number' => '605100', 'name' => 'Autres achats (fournitures)', 'class_number' => 6, 'type' => 'expense'],
            ['number' => '661100', 'name' => 'Rémunérations du personnel', 'class_number' => 6, 'type' => 'expense'],
            ['number' => '664100', 'name' => 'Charges sociales patronales', 'class_number' => 6, 'type' => 'expense'],
            ['number' => '681100', 'name' => 'Dotations aux amortissements', 'class_number' => 6, 'type' => 'expense'],
            ['number' => '701100', 'name' => 'Ventes de produits finis', 'class_number' => 7, 'type' => 'revenue'],
            ['number' => '706100', 'name' => 'Prestations de services', 'class_number' => 7, 'type' => 'revenue'],
        ];

        $created = 0;
        foreach ($accounts as $acc) {
            if ($this->existsIn('accounts', ['company_id' => $this->company->id, 'number' => $acc['number']])) continue;
            if ($this->insertRow('accounts', $acc)) $created++;
        }
        echo "📒 Plan comptable : {$created} compte(s) SYSCOHADA\n";
    }

    // ═══════════════════ JOURNAUX ═══════════════════

    protected function seedJournals(): void
    {
        if (!$this->hasTable('journals')) { echo "⚠ Table journals absente\n"; return; }

        $journals = [
            ['code' => 'AC', 'name' => 'Journal des Achats', 'type' => 'purchase'],
            ['code' => 'VE', 'name' => 'Journal des Ventes', 'type' => 'sales'],
            ['code' => 'BQ', 'name' => 'Journal de Banque', 'type' => 'bank'],
            ['code' => 'OD', 'name' => 'Opérations Diverses', 'type' => 'misc'],
            ['code' => 'PA', 'name' => 'Journal de Paie', 'type' => 'payroll'],
        ];

        $created = 0;
        foreach ($journals as $j) {
            if ($this->existsIn('journals', ['company_id' => $this->company->id, 'code' => $j['code']])) continue;
            if ($this->insertRow('journals', $j)) $created++;
        }
        echo "📓 Journaux comptables : {$created} créé(s)\n";
    }

    // ═══════════════════ RH : DÉPARTEMENTS ═══════════════════

    protected function seedDepartments(): void
    {
        if (!$this->hasTable('departments')) { echo "⚠ Table departments absente\n"; return; }

        $departments = [
            ['code' => 'DG', 'name' => 'Direction Générale'],
            ['code' => 'CF', 'name' => 'Comptabilité & Finance'],
            ['code' => 'RH', 'name' => 'Ressources Humaines'],
            ['code' => 'CM', 'name' => 'Commercial & Marketing'],
            ['code' => 'IT', 'name' => 'Informatique & Digital'],
        ];

        foreach ($departments as $d) {
            try {
                $existing = DB::table('departments')->where('code', $d['code'])->where('company_id', $this->company->id)->first();
                if ($existing) { $this->deptIds[$d['code']] = $existing->id; continue; }
                $id = $this->insertRow('departments', $d);
                if ($id) $this->deptIds[$d['code']] = $id;
            } catch (\Throwable $e) {}
        }
        echo "🏢 Départements : " . count($this->deptIds) . "\n";
    }

    // ═══════════════════ RH : POSTES ═══════════════════

    protected function seedPositions(): void
    {
        if (!$this->hasTable('positions')) { echo "⚠ Table positions absente\n"; return; }

        $positions = [
            ['code' => 'DIR-01', 'name' => 'Directeur Général', 'dept' => 'DG'],
            ['code' => 'CMP-01', 'name' => 'Comptable', 'dept' => 'CF'],
            ['code' => 'AID-01', 'name' => 'Aide-comptable', 'dept' => 'CF'],
            ['code' => 'RH-01', 'name' => 'Responsable RH', 'dept' => 'RH'],
            ['code' => 'COM-01', 'name' => 'Commercial', 'dept' => 'CM'],
            ['code' => 'DEV-01', 'name' => 'Développeur', 'dept' => 'IT'],
            ['code' => 'AST-01', 'name' => 'Assistant(e) de direction', 'dept' => 'DG'],
        ];

        foreach ($positions as $p) {
            try {
                $existing = DB::table('positions')->where('code', $p['code'])->where('company_id', $this->company->id)->first();
                if ($existing) { $this->posIds[$p['code']] = $existing->id; continue; }
                $id = $this->insertRow('positions', [
                    'code' => $p['code'],
                    'name' => $p['name'],
                    'department_id' => $this->deptIds[$p['dept']] ?? null,
                ]);
                if ($id) $this->posIds[$p['code']] = $id;
            } catch (\Throwable $e) {}
        }
        echo "💼 Postes : " . count($this->posIds) . "\n";
    }

    // ═══════════════════ RH : EMPLOYÉS ═══════════════════

    protected function seedEmployees(int $companyIndex): void
    {
        if (!$this->hasTable('employees')) { echo "⚠ Table employees absente\n"; return; }

        $domain = $companyIndex === 0 ? 'fiducia-africa.com' : 'fiducia-consulting.com';

        $employees = [
            ['matricule' => 'EMP-001', 'first_name' => 'Jean-Baptiste', 'last_name' => 'KOUASSI', 'email' => "jb.kouassi@{$domain}", 'phone' => '+225 07 01 02 03 04', 'hire_date' => '2019-03-15', 'base_salary' => 1500000, 'dept' => 'DG', 'pos' => 'DIR-01'],
            ['matricule' => 'EMP-002', 'first_name' => 'Awa', 'last_name' => 'KONÉ', 'email' => "awa.kone@{$domain}", 'phone' => '+225 07 05 06 07 08', 'hire_date' => '2020-06-01', 'base_salary' => 650000, 'dept' => 'CF', 'pos' => 'CMP-01'],
            ['matricule' => 'EMP-003', 'first_name' => 'Moussa', 'last_name' => 'TRAORÉ', 'email' => "moussa.traore@{$domain}", 'phone' => '+225 05 09 10 11 12', 'hire_date' => '2019-09-10', 'base_salary' => 700000, 'dept' => 'RH', 'pos' => 'RH-01'],
            ['matricule' => 'EMP-004', 'first_name' => 'Fatou', 'last_name' => 'DIABATÉ', 'email' => "fatou.diabate@{$domain}", 'phone' => '+225 05 13 14 15 16', 'hire_date' => '2021-02-14', 'base_salary' => 550000, 'dept' => 'CM', 'pos' => 'COM-01'],
            ['matricule' => 'EMP-005', 'first_name' => "N'Guessan", 'last_name' => 'YAO', 'email' => "nguessan.yao@{$domain}", 'phone' => '+225 07 17 18 19 20', 'hire_date' => '2022-01-05', 'base_salary' => 600000, 'dept' => 'IT', 'pos' => 'DEV-01'],
            ['matricule' => 'EMP-006', 'first_name' => 'Mariam', 'last_name' => 'BAMBA', 'email' => "mariam.bamba@{$domain}", 'phone' => '+225 05 21 22 23 24', 'hire_date' => '2022-08-22', 'base_salary' => 350000, 'dept' => 'DG', 'pos' => 'AST-01'],
            ['matricule' => 'EMP-007', 'first_name' => 'Ibrahim', 'last_name' => 'OUATTARA', 'email' => "ibrahim.ouattara@{$domain}", 'phone' => '+225 07 25 26 27 28', 'hire_date' => '2023-04-03', 'base_salary' => 500000, 'dept' => 'CM', 'pos' => 'COM-01'],
            ['matricule' => 'EMP-008', 'first_name' => 'Aminata', 'last_name' => 'COULIBALY', 'email' => "aminata.coulibaly@{$domain}", 'phone' => '+225 05 29 30 31 32', 'hire_date' => '2023-10-16', 'base_salary' => 480000, 'dept' => 'CF', 'pos' => 'AID-01'],
        ];

        $created = 0;
        foreach ($employees as $emp) {
            if ($this->existsIn('employees', ['company_id' => $this->company->id, 'matricule' => $emp['matricule']])) continue;

            $id = $this->insertRow('employees', [
                'matricule' => $emp['matricule'],
                'first_name' => $emp['first_name'],
                'last_name' => $emp['last_name'],
                'email' => $emp['email'],
                'phone' => $emp['phone'],
                'hire_date' => $emp['hire_date'],
                'status' => 'active',
                'base_salary' => $emp['base_salary'],
                'salary' => $emp['base_salary'],
                'department_id' => $this->deptIds[$emp['dept']] ?? null,
                'position_id' => $this->posIds[$emp['pos']] ?? null,
                'employment_date' => $emp['hire_date'],
            ]);
            if ($id) $created++;
        }
        echo "🧑‍💼 Employés : {$created} créé(s)\n";
    }

    // ═══════════════════ RH : CONTRATS ═══════════════════

    protected function seedContracts(): void
    {
        if (!$this->hasTable('contracts')) { echo "⚠ Table contracts absente\n"; return; }

        $employees = DB::table('employees')->where('company_id', $this->company->id)->get();
        $created = 0;

        foreach ($employees as $emp) {
            try {
                if (DB::table('contracts')->where('employee_id', $emp->id)->exists()) continue;
            } catch (\Throwable $e) { continue; }

            $id = $this->insertRow('contracts', [
                'employee_id' => $emp->id,
                'contract_type' => 'CDI',
                'type' => 'CDI',
                'start_date' => $emp->hire_date ?? now()->toDateString(),
                'status' => 'active',
                'salary' => $emp->base_salary ?? $emp->salary ?? 0,
                'base_salary' => $emp->base_salary ?? 0,
            ]);
            if ($id) $created++;
        }
        echo "📝 Contrats : {$created} CDI\n";
    }

    // ═══════════════════ PAIE : RUBRIQUES ═══════════════════

    protected function seedPayItems(): void
    {
        if (!$this->hasTable('pay_items')) { echo "⚠ Table pay_items absente\n"; return; }

        $items = [
            ['code' => 'SAL_BASE', 'name' => 'Salaire de base', 'type' => 'earning', 'calculation_method' => 'fixed', 'rate' => null, 'fixed_amount' => null],
            ['code' => 'PRIME_ANC', 'name' => "Prime d'ancienneté", 'type' => 'earning', 'calculation_method' => 'percentage', 'rate' => 5, 'fixed_amount' => null],
            ['code' => 'PRIME_TRANS', 'name' => 'Prime de transport', 'type' => 'earning', 'calculation_method' => 'fixed', 'rate' => null, 'fixed_amount' => 60000],
            ['code' => 'HS_25', 'name' => 'Heures supplémentaires 25%', 'type' => 'earning', 'calculation_method' => 'percentage', 'rate' => 25, 'fixed_amount' => null],
        ];

        $created = 0;
        foreach ($items as $item) {
            if ($this->existsIn('pay_items', ['code' => $item['code']])) continue;
            if ($this->insertRow('pay_items', $item)) $created++;
        }
        echo "💵 Rubriques de paie : {$created}\n";
    }

    // ═══════════════════ PAIE : COTISATIONS ═══════════════════

    protected function seedContributions(): void
    {
        if (!$this->hasTable('social_contributions')) { echo "⚠ Table social_contributions absente\n"; return; }

        $contribs = [
            ['code' => 'CNPS_RP', 'name' => 'CNPS Retraite Plafonnée', 'organism' => 'CNPS', 'employee_rate' => 4.25, 'employer_rate' => 7.7, 'ceiling' => 1200000, 'effective_from' => now()->startOfYear()->toDateString()],
            ['code' => 'CNPS_AT', 'name' => 'Accidents du travail', 'organism' => 'CNPS', 'employee_rate' => 0, 'employer_rate' => 2.0, 'ceiling' => null, 'effective_from' => now()->startOfYear()->toDateString()],
            ['code' => 'CNPS_PF', 'name' => 'Prestations familiales', 'organism' => 'CNPS', 'employee_rate' => 0, 'employer_rate' => 5.75, 'ceiling' => null, 'effective_from' => now()->startOfYear()->toDateString()],
            ['code' => 'IR_ITS', 'name' => 'Impôt sur les salaires', 'organism' => 'État', 'employee_rate' => 5, 'employer_rate' => 0, 'ceiling' => null, 'effective_from' => now()->startOfYear()->toDateString()],
        ];

        $created = 0;
        foreach ($contribs as $c) {
            if ($this->existsIn('social_contributions', ['code' => $c['code']])) continue;
            if ($this->insertRow('social_contributions', $c)) $created++;
        }
        echo "🏥 Cotisations sociales : {$created}\n";
    }

    // ═══════════════════ PAIE : PÉRIODE + BULLETINS ═══════════════════

    protected function seedPayroll(): void
    {
        if (!$this->hasTable('pay_runs')) { echo "⚠ Table pay_runs absente\n"; return; }

        $periodStart = now()->startOfMonth();
        $periodEnd = now()->endOfMonth();
        $reference = 'PAIE-' . now()->format('Y-m');

        // Période de paie
        try {
            $run = DB::table('pay_runs')->where('company_id', $this->company->id)->where('reference', $reference)->first();
        } catch (\Throwable $e) { $run = null; }

        if (!$run) {
            $runId = $this->insertRow('pay_runs', [
                'name' => 'Paie ' . now()->format('F Y'),
                'reference' => $reference,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'payment_date' => $periodEnd->toDateString(),
                'status' => 'calculated',
            ]);
        } else {
            $runId = $run->id;
        }

        if (!$runId) { echo "⚠ Période de paie non créée\n"; return; }

        // Bulletins pour chaque employé
        $employees = DB::table('employees')->where('company_id', $this->company->id)->get();
        $created = 0;

        foreach ($employees as $emp) {
            $base = (float) ($emp->base_salary ?? $emp->salary ?? 0);
            if ($base <= 0) continue;

            try {
                if ($this->hasTable('payslips') && DB::table('payslips')->where('pay_run_id', $runId)->where('employee_id', $emp->id)->exists()) continue;
            } catch (\Throwable $e) {}

            // Calculs
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

            $payslipId = $this->insertRow('payslips', [
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

            // Rubriques du bulletin
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
                    $this->insertRow('payslip_items', array_merge($item, ['payslip_id' => $payslipId]));
                }
            }

            $created++;
        }

        echo "📅 Période de paie : {$reference}\n";
        echo "📄 Bulletins générés : {$created}\n";
    }

    // ═══════════════════ VENTES : CLIENTS ═══════════════════

    protected function seedClients(int $companyIndex): void
    {
        if (!$this->hasTable('clients')) { echo "⚠ Table clients absente\n"; return; }

        $domain = $companyIndex === 0 ? 'sid-ci.com' : 'atlantique.ci';
        $clients = [
            ['code' => 'CLI-001', 'name' => 'SOCIÉTÉ IVOIRIENNE DE DISTRIBUTION', 'email' => "contact@{$domain}", 'phone' => '+225 27 20 21 22 23'],
            ['code' => 'CLI-002', 'name' => 'GROUPE ATLANTIQUE CI', 'email' => "info@groupe-atlantique.{$companyIndex}", 'phone' => '+225 27 21 30 40 50'],
            ['code' => 'CLI-003', 'name' => 'PHARMACIE DU PLATEAU', 'email' => 'pharma.plateau@gmail.com', 'phone' => '+225 27 20 31 41 51'],
            ['code' => 'CLI-004', 'name' => 'TRANSPORTS BOUAKÉ SARL', 'email' => 'transports.bouake@yahoo.fr', 'phone' => '+225 25 60 11 22 33'],
        ];

        $created = 0;
        foreach ($clients as $cl) {
            if ($this->existsIn('clients', ['company_id' => $this->company->id, 'code' => $cl['code']])) continue;
            if ($this->insertRow('clients', $cl)) $created++;
        }
        echo "🤝 Clients : {$created}\n";
    }

    // ═══════════════════ VENTES : FACTURES ═══════════════════

    protected function seedSalesInvoices(): void
    {
        if (!$this->hasTable('sales_invoices')) { echo "⚠ Table sales_invoices absente\n"; return; }

        $clients = DB::table('clients')->where('company_id', $this->company->id)->get();
        if ($clients->isEmpty()) { echo "⚠ Aucun client, factures vente ignorées\n"; return; }

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
            $ref = $inv['number'];
            try {
                $cols = $this->cols('sales_invoices');
                $checkCol = in_array('number', $cols) ? 'number' : (in_array('reference', $cols) ? 'reference' : null);
                if ($checkCol && DB::table('sales_invoices')->where($checkCol, $ref)->where('company_id', $this->company->id)->exists()) continue;
            } catch (\Throwable $e) {}

            $client = $clients[$inv['client']] ?? $clients[0];
            $vat = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $vat;
            $date = now()->subDays($inv['days']);

            $id = $this->insertRow('sales_invoices', [
                'client_id' => $client->id,
                'number' => $ref,
                'reference' => $ref,
                'issue_date' => $date->toDateString(),
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
        echo "🧾 Factures de vente : {$created}\n";
    }

    // ═══════════════════ ACHATS : FOURNISSEURS ═══════════════════

    protected function seedSuppliers(int $companyIndex): void
    {
        if (!$this->hasTable('suppliers')) { echo "⚠ Table suppliers absente\n"; return; }

        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'FOURNITURES BUREAU PLUS', 'email' => 'contact@fbplus.ci', 'phone' => '+225 27 22 44 55 66'],
            ['code' => 'SUP-002', 'name' => 'CFAO TECHNOLOGY CI', 'email' => 'ventes@cfao.ci', 'phone' => '+225 27 21 77 88 99'],
            ['code' => 'SUP-003', 'name' => 'IMPRIMERIE VIE NOUVELLE', 'email' => 'ivn@imprimerie.ci', 'phone' => '+225 27 20 12 34 56'],
            ['code' => 'SUP-004', 'name' => 'CI ÉNERGIE SERVICES', 'email' => 'contact@ci-energie.com', 'phone' => '+225 25 23 45 67 89'],
        ];

        $created = 0;
        foreach ($suppliers as $s) {
            if ($this->existsIn('suppliers', ['company_id' => $this->company->id, 'code' => $s['code']])) continue;
            if ($this->insertRow('suppliers', $s)) $created++;
        }
        echo "🏭 Fournisseurs : {$created}\n";
    }

    // ═══════════════════ ACHATS : FACTURES ═══════════════════

    protected function seedPurchaseInvoices(): void
    {
        if (!$this->hasTable('purchase_invoices')) { echo "⚠ Table purchase_invoices absente\n"; return; }

        $suppliers = DB::table('suppliers')->where('company_id', $this->company->id)->get();
        if ($suppliers->isEmpty()) { echo "⚠ Aucun fournisseur, factures achat ignorées\n"; return; }

        $year = now()->format('Y');
        $invoices = [
            ['number' => "FAF-{$year}-0001", 'supplier' => 0, 'ht' => 850000, 'days' => 55, 'status' => 'paid'],
            ['number' => "FAF-{$year}-0002", 'supplier' => 1, 'ht' => 1200000, 'days' => 30, 'status' => 'pending'],
            ['number' => "FAF-{$year}-0003", 'supplier' => 2, 'ht' => 450000, 'days' => 10, 'status' => 'pending'],
        ];

        $created = 0;
        foreach ($invoices as $inv) {
            $ref = $inv['number'];
            try {
                $cols = $this->cols('purchase_invoices');
                $checkCol = in_array('number', $cols) ? 'number' : (in_array('reference', $cols) ? 'reference' : null);
                if ($checkCol && DB::table('purchase_invoices')->where($checkCol, $ref)->where('company_id', $this->company->id)->exists()) continue;
            } catch (\Throwable $e) {}

            $supplier = $suppliers[$inv['supplier']] ?? $suppliers[0];
            $vat = round($inv['ht'] * 0.18);
            $ttc = $inv['ht'] + $vat;
            $date = now()->subDays($inv['days']);

            $id = $this->insertRow('purchase_invoices', [
                'supplier_id' => $supplier->id,
                'number' => $ref,
                'reference' => $ref,
                'issue_date' => $date->toDateString(),
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
        echo "📥 Factures d'achat : {$created}\n";
    }

    // ═══════════════════ IMMOBILISATIONS ═══════════════════

    protected function seedAssets(): void
    {
        if (!$this->hasTable('assets')) { echo "⚠ Table assets absente\n"; return; }

        $assets = [
            ['code' => 'IMM-001', 'name' => 'Véhicule Toyota Hilux', 'category' => 'Matériel de transport', 'acquisition_date' => '2023-05-10', 'acquisition_value' => 18500000, 'cost' => 18500000, 'useful_life' => 5, 'status' => 'in_use'],
            ['code' => 'IMM-002', 'name' => 'Parc informatique (10 postes)', 'category' => 'Matériel informatique', 'acquisition_date' => '2024-01-15', 'acquisition_value' => 4500000, 'cost' => 4500000, 'useful_life' => 3, 'status' => 'in_use'],
            ['code' => 'IMM-003', 'name' => 'Mobilier de bureau', 'category' => 'Mobilier', 'acquisition_date' => '2023-08-20', 'acquisition_value' => 2200000, 'cost' => 2200000, 'useful_life' => 10, 'status' => 'in_use'],
        ];

        $created = 0;
        foreach ($assets as $a) {
            if ($this->existsIn('assets', ['company_id' => $this->company->id, 'code' => $a['code']])) continue;
            if ($this->insertRow('assets', $a)) $created++;
        }
        echo "🏗️ Immobilisations : {$created}\n";
    }

    // ═══════════════════ DEVISES ═══════════════════

    protected function seedExchangeRates(): void
    {
        if (!$this->hasTable('exchange_rates')) { echo "⚠ Table exchange_rates absente\n"; return; }

        $rates = [
            ['currency_code' => 'EUR', 'currency_name' => 'Euro', 'rate_to_base' => 655.957],
            ['currency_code' => 'USD', 'currency_name' => 'Dollar US', 'rate_to_base' => 605.50],
            ['currency_code' => 'GBP', 'currency_name' => 'Livre sterling', 'rate_to_base' => 765.25],
            ['currency_code' => 'CNY', 'currency_name' => 'Yuan chinois', 'rate_to_base' => 83.75],
        ];

        $created = 0;
        foreach ($rates as $r) {
            if ($this->existsIn('exchange_rates', ['company_id' => $this->company->id, 'currency_code' => $r['currency_code']])) continue;
            $id = $this->insertRow('exchange_rates', array_merge($r, [
                'effective_from' => now()->startOfYear()->toDateString(),
                'is_active' => true,
            ]));
            if ($id) $created++;
        }
        echo "💱 Taux de change : {$created}\n";
    }

    // ═══════════════════ FISCALITÉ ═══════════════════

    protected function seedTaxDeclarations(): void
    {
        if (!$this->hasTable('vat_declarations')) { echo "⚠ Table vat_declarations absente\n"; return; }

        $created = 0;
        foreach ([2, 1] as $i) {
            $date = now()->subMonths($i);
            if ($this->existsIn('vat_declarations', ['company_id' => $this->company->id, 'year' => $date->year, 'month' => $date->month])) continue;

            $id = $this->insertRow('vat_declarations', [
                'year' => $date->year,
                'month' => $date->month,
                'period_start' => $date->copy()->startOfMonth()->toDateString(),
                'period_end' => $date->copy()->endOfMonth()->toDateString(),
                'taxable_amount' => 4500000,
                'vat_amount' => 810000,
                'total_vat' => 810000,
                'vat_due' => 810000,
                'status' => 'filed',
                'filed_at' => $date->copy()->endOfMonth()->toDateString(),
            ]);
            if ($id) $created++;
        }
        echo "🏛️ Déclarations TVA : {$created}\n";

        // Échéances fiscales
        if ($this->hasTable('fiscal_deadlines')) {
            $deadlines = [
                ['name' => 'Déclaration TVA mensuelle', 'deadline_date' => now()->copy()->day(15)->toDateString(), 'status' => 'pending'],
                ['name' => 'Déclaration CNPS', 'deadline_date' => now()->copy()->day(10)->toDateString(), 'status' => 'pending'],
                ['name' => 'Acompte IS', 'deadline_date' => now()->copy()->addMonth()->day(30)->toDateString(), 'status' => 'pending'],
            ];
            $c2 = 0;
            foreach ($deadlines as $d) {
                if ($this->insertRow('fiscal_deadlines', $d)) $c2++;
            }
            if ($c2 > 0) echo "⏰ Échéances fiscales : {$c2}\n";
        }
    }

    // ═══════════════════ ÉCRITURES COMPTABLES ═══════════════════

    protected function createEntry(array $entry, array $items): ?int
    {
        if (!$this->hasTable('journal_entries') || !$this->hasTable('journal_items')) return null;
        if ($this->existsIn('journal_entries', ['company_id' => $this->company->id, 'reference' => $entry['reference']])) return null;

        $entryCols = $this->cols('journal_entries');
        $row = array_intersect_key($entry, array_flip($entryCols));
        $row['company_id'] = $this->company->id;

        if (in_array('journal_id', $entryCols) && $this->hasTable('journals')) {
            try {
                $j = DB::table('journals')->where('code', $entry['journal_code'] ?? 'OD')->first();
                if ($j) $row['journal_id'] = $j->id;
            } catch (\Throwable $e) {}
        }
        if (in_array('created_at', $entryCols)) $row['created_at'] = now();
        if (in_array('updated_at', $entryCols)) $row['updated_at'] = now();

        try {
            $entryId = DB::table('journal_entries')->insertGetId($row);
        } catch (\Throwable $e) {
            return null;
        }

        $itemCols = $this->cols('journal_items');
        foreach ($items as $item) {
            $r = [];
            if (in_array('entry_id', $itemCols)) $r['entry_id'] = $entryId;
            if (in_array('journal_entry_id', $itemCols)) $r['journal_entry_id'] = $entryId;
            if (in_array('company_id', $itemCols)) $r['company_id'] = $this->company->id;
            if (in_array('account_number', $itemCols)) $r['account_number'] = $item['account'];
            if (in_array('account_id', $itemCols)) {
                try {
                    $acc = DB::table('accounts')->where('number', $item['account'])->first();
                    if ($acc) $r['account_id'] = $acc->id;
                } catch (\Throwable $e) {}
            }
            if (in_array('label', $itemCols)) $r['label'] = $item['description'] ?? '';
            if (in_array('description', $itemCols)) $r['description'] = $item['description'] ?? '';
            if (in_array('debit', $itemCols)) $r['debit'] = $item['debit'];
            if (in_array('credit', $itemCols)) $r['credit'] = $item['credit'];
            if (in_array('created_at', $itemCols)) $r['created_at'] = now();
            if (in_array('updated_at', $itemCols)) $r['updated_at'] = now();
            try { DB::table('journal_items')->insert($r); } catch (\Throwable $e) {}
        }

        return $entryId;
    }

    protected function seedJournalEntries(): void
    {
        $year = now()->format('Y');

        $entries = [
            [
                'entry' => ['entry_date' => now()->subDays(45)->toDateString(), 'journal_code' => 'VE', 'reference' => "VE-{$year}-001", 'description' => 'Facture client SID', 'status' => 'posted'],
                'items' => [
                    ['account' => '411100', 'debit' => 2360000, 'credit' => 0, 'description' => 'Créance client SID'],
                    ['account' => '701100', 'debit' => 0, 'credit' => 2000000, 'description' => 'Ventes de produits'],
                    ['account' => '443100', 'debit' => 0, 'credit' => 360000, 'description' => 'TVA facturée 18%'],
                ],
            ],
            [
                'entry' => ['entry_date' => now()->subDays(38)->toDateString(), 'journal_code' => 'AC', 'reference' => "AC-{$year}-001", 'description' => 'Achat fournitures bureau', 'status' => 'posted'],
                'items' => [
                    ['account' => '601100', 'debit' => 850000, 'credit' => 0, 'description' => 'Achats'],
                    ['account' => '445100', 'debit' => 153000, 'credit' => 0, 'description' => 'TVA déductible'],
                    ['account' => '401100', 'debit' => 0, 'credit' => 1003000, 'description' => 'Dette fournisseur'],
                ],
            ],
            [
                'entry' => ['entry_date' => now()->subDays(20)->toDateString(), 'journal_code' => 'BQ', 'reference' => "BQ-{$year}-001", 'description' => 'Encaissement virement client', 'status' => 'posted'],
                'items' => [
                    ['account' => '521100', 'debit' => 1180000, 'credit' => 0, 'description' => 'Virement reçu'],
                    ['account' => '411100', 'debit' => 0, 'credit' => 1180000, 'description' => 'Règlement client'],
                ],
            ],
            [
                'entry' => ['entry_date' => now()->subDays(10)->toDateString(), 'journal_code' => 'OD', 'reference' => "OD-{$year}-001", 'description' => 'Dotation aux amortissements', 'status' => 'posted'],
                'items' => [
                    ['account' => '681100', 'debit' => 250000, 'credit' => 0, 'description' => 'Dotations'],
                    ['account' => '281300', 'debit' => 0, 'credit' => 250000, 'description' => 'Amortissements cumulés'],
                ],
            ],
            [
                'entry' => ['entry_date' => now()->subDays(5)->toDateString(), 'journal_code' => 'PA', 'reference' => 'PA-' . now()->format('Y-m'), 'description' => 'Comptabilisation de la paie', 'status' => 'posted'],
                'items' => [
                    ['account' => '661100', 'debit' => 5000000, 'credit' => 0, 'description' => 'Rémunérations'],
                    ['account' => '664100', 'debit' => 775000, 'credit' => 0, 'description' => 'Charges patronales'],
                    ['account' => '421100', 'debit' => 0, 'credit' => 4450000, 'description' => 'Salaires nets à payer'],
                    ['account' => '431100', 'debit' => 0, 'credit' => 900000, 'description' => 'CNPS à payer'],
                    ['account' => '442100', 'debit' => 0, 'credit' => 425000, 'description' => 'Impôts à reverser'],
                ],
            ],
        ];

        $created = 0;
        foreach ($entries as $e) {
            if ($this->createEntry($e['entry'], $e['items'])) $created++;
        }
        echo "✍️ Écritures comptables : {$created} (équilibrées Débit/Crédit)\n";
    }

    // ═══════════════════ RÉCAPITULATIF ═══════════════════

    protected function printSummary(): void
    {
        echo "📊 RÉCAPITULATIF DE LA BASE :\n\n";
        $tables = ['users', 'companies', 'accounts', 'journals', 'journal_entries', 'journal_items',
                   'departments', 'positions', 'employees', 'contracts',
                   'pay_runs', 'payslips', 'payslip_items', 'pay_items', 'social_contributions',
                   'clients', 'sales_invoices', 'suppliers', 'purchase_invoices',
                   'assets', 'exchange_rates', 'vat_declarations', 'taxes', 'settings'];

        foreach ($tables as $t) {
            if ($this->hasTable($t)) {
                try {
                    $count = DB::table($t)->count();
                    $bar = $count > 0 ? str_repeat('█', min($count, 25)) : '·';
                    printf("   %-22s %4d  %s\n", $t, $count, $bar);
                } catch (\Throwable $e) {}
            }
        }
        echo "\n✅ TERMINÉ ! Rafraîchissez le navigateur (Ctrl + F5)\n";
    }
}
