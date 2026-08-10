<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateTenant extends Command
{
    protected $signature = 'tenant:create
        {--company= : Nom de l\'entreprise}
        {--admin-name= : Nom de l\'administrateur}
        {--admin-email= : Email de l\'administrateur}
        {--admin-password= : Mot de passe}
        {--currency=XOF : Devise}
        {--timezone=Africa/Abidjan : Fuseau horaire}';

    protected $description = 'Créer une entreprise avec configuration minimale';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════');
        $this->info('   🏢 CRÉATION TENANT (ENTREPRISE)');
        $this->info('═══════════════════════════════════════');

        // 1. Nom de l'entreprise
        $companyName = $this->option('company') ?? $this->ask('Nom de l\'entreprise');
        if (empty($companyName)) { $this->error('✗ Nom requis'); return 1; }

        if (Company::where('name', $companyName)->exists()) {
            $this->error("✗ Une entreprise '$companyName' existe déjà");
            return 1;
        }

        // 2. Admin
        $adminName = $this->option('admin-name') ?? $this->ask('Nom de l\'administrateur');
        $adminEmail = $this->option('admin-email');
        while (!$adminEmail || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $adminEmail = $this->ask('Email de l\'administrateur');
        }

        $adminPassword = $this->option('admin-password');
        if (!$adminPassword) {
            $adminPassword = $this->secret('Mot de passe (min 8 car.)');
            if (strlen($adminPassword) < 8) { $this->error('✗ Trop court'); return 1; }
        }

        $currency = $this->option('currency');
        $timezone = $this->option('timezone');

        // 3. Créer l'entreprise
        $slug = Str::slug($companyName) . '-' . Str::random(4);
        $company = Company::create([
            'name' => $companyName,
            'slug' => $slug,
            'short_name' => Str::limit($companyName, 20),
            'currency' => $currency,
            'timezone' => $timezone,
            'is_active' => true,
        ]);
        $this->info("✓ Entreprise créée (ID: {$company->id})");

        // 4. Configuration minimale via DB direct
        $this->setupMinimalDefaults($company);

        // 5. Créer l'admin
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
            ]
        );

        $admin->companies()->syncWithoutDetaching([
            $company->id => ['role' => 'admin', 'is_active' => true]
        ]);

        // 6. Rôle admin-company
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin-company', 'guard_name' => 'web']);
            $admin->assignRole($role);
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  ✅ TENANT CRÉÉ AVEC SUCCÈS');
        $this->info('═══════════════════════════════════════');
        $this->line("  Entreprise : <fg=cyan>$companyName</> (ID: {$company->id})");
        $this->line("  Admin      : <fg=green>$adminEmail</>");
        $this->info('═══════════════════════════════════════');

        return 0;
    }

    private function setupMinimalDefaults(Company $company): void
    {
        // Utiliser DB::table() directement pour éviter les problèmes de namespace
        
        // 1. Plan comptable
        if (DB::table('chart_accounts')->where('company_id', $company->id)->count() === 0) {
            DB::table('chart_accounts')->insert([
                'company_id' => $company->id,
                'name' => 'Plan SYSCOHADA Standard',
                'slug' => 'syscohada-' . $company->id,
                'standard' => 'SYSCOHADA',
                'version' => '2024',
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("  ✓ Plan comptable créé");
        }

        // 2. Journaux comptables
        $journals = [
            ['code' => 'VE', 'name' => 'Ventes', 'type' => 'sale'],
            ['code' => 'AC', 'name' => 'Achats', 'type' => 'purchase'],
            ['code' => 'BQ', 'name' => 'Banque', 'type' => 'bank'],
            ['code' => 'CA', 'name' => 'Caisse', 'type' => 'cash'],
            ['code' => 'OD', 'name' => 'Opérations Diverses', 'type' => 'misc'],
            ['code' => 'PA', 'name' => 'Journal de Paie', 'type' => 'payroll'],
        ];

        foreach ($journals as $j) {
            if (DB::table('journals')->where('company_id', $company->id)->where('code', $j['code'])->count() === 0) {
                DB::table('journals')->insert([
                    'company_id' => $company->id,
                    'code' => $j['code'],
                    'name' => $j['name'],
                    'type' => $j['type'],
                    'next_number' => 1,
                    'is_active' => true,
                    'requires_attachment' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info("  ✓ Journaux comptables créés");

        // 3. Départements
        $depts = [
            ['code' => 'DG', 'name' => 'Direction Générale'],
            ['code' => 'CF', 'name' => 'Comptabilité & Finance'],
            ['code' => 'RH', 'name' => 'Ressources Humaines'],
            ['code' => 'CM', 'name' => 'Commercial & Marketing'],
            ['code' => 'IT', 'name' => 'Informatique'],
        ];

        foreach ($depts as $d) {
            if (DB::table('departments')->where('company_id', $company->id)->where('code', $d['code'])->count() === 0) {
                DB::table('departments')->insert([
                    'company_id' => $company->id,
                    'code' => $d['code'],
                    'name' => $d['name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info("  ✓ Départements créés");

        // 4. Cotisations sociales (si table existe)
        if (DB::table('social_contributions')->count() === 0) {
            $contribs = [
                ['code' => 'CNPS_RET', 'name' => 'CNPS - Retraite', 'emp_rate' => 4.80, 'er_rate' => 7.70],
                ['code' => 'CNPS_PF', 'name' => 'CNPS - Prestations Familiales', 'emp_rate' => 0, 'er_rate' => 5.00],
                ['code' => 'CNPS_AT', 'name' => 'CNPS - Accidents du Travail', 'emp_rate' => 0, 'er_rate' => 2.00],
            ];

            foreach ($contribs as $c) {
                $id = DB::table('social_contributions')->insertGetId([
                    'code' => $c['code'],
                    'name' => $c['name'],
                    'organism' => 'CNPS',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('social_contribution_rates')->insert([
                    'social_contribution_id' => $id,
                    'employee_rate' => $c['emp_rate'],
                    'employer_rate' => $c['er_rate'],
                    'ceiling' => 500000,
                    'effective_from' => '2020-01-01',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->info("  ✓ Cotisations sociales créées");
        }

        // 5. TVA (si table existe)
        if (DB::table('taxes')->where('company_id', $company->id)->where('code', 'TVA_18')->count() === 0) {
            DB::table('taxes')->insert([
                'company_id' => $company->id,
                'name' => 'TVA 18%',
                'code' => 'TVA_18',
                'type' => 'vat',
                'scope' => 'both',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->info("  ✓ TVA créée");
        }

        // 6. Settings
        $settings = [
            ['key' => 'language', 'value' => 'fr', 'group' => 'general'],
            ['key' => 'invoice_payment_days', 'value' => '30', 'group' => 'general'],
        ];

        foreach ($settings as $s) {
            if (DB::table('settings')->where('company_id', $company->id)->where('key', $s['key'])->count() === 0) {
                DB::table('settings')->insert([
                    'company_id' => $company->id,
                    'key' => $s['key'],
                    'value' => $s['value'],
                    'group' => $s['group'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        $this->info("  ✓ Paramètres créés");
    }
}
