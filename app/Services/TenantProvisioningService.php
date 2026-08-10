<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantProvisioningService
{
    /**
     * Créer une entreprise complète avec configuration par défaut
     */
    public function createCompany(array $data): Company
    {
        return DB::transaction(function () use ($data) {
            $slug = Str::slug($data['name']) . '-' . Str::random(4);

            $company = Company::create([
                'name' => $data['name'],
                'slug' => $slug,
                'short_name' => $data['short_name'] ?? Str::limit($data['name'], 20),
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'rccm' => $data['rccm'] ?? null,
                'tax_id' => $data['tax_id'] ?? null,
                'currency' => $data['currency'] ?? 'XOF',
                'timezone' => $data['timezone'] ?? 'Africa/Abidjan',
                'is_active' => true,
            ]);

            $this->setupDefaults($company);

            return $company;
        });
    }

    /**
     * Créer un utilisateur et l'attacher à une entreprise
     */
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            if (!empty($data['company_id'])) {
                $user->companies()->syncWithoutDetaching([
                    $data['company_id'] => [
                        'role' => $data['role'] ?? 'employee',
                        'is_active' => true,
                    ]
                ]);
            }

            // Assigner le rôle Spatie
            if (class_exists(\Spatie\Permission\Models\Role::class)) {
                $roleName = $data['role'] ?? 'employee';
                $role = \Spatie\Permission\Models\Role::firstOrCreate([
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);
                $user->syncRoles([$role]);
            }

            return $user;
        });
    }

    /**
     * Configuration par défaut d'une entreprise
     */
    private function setupDefaults(Company $company): void
    {
        // 1. Plan comptable SYSCOHADA
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

        // 2. Journaux comptables
        $journals = [
            ['code' => 'VE', 'name' => 'Journal des Ventes', 'type' => 'sale'],
            ['code' => 'AC', 'name' => 'Journal des Achats', 'type' => 'purchase'],
            ['code' => 'BQ', 'name' => 'Journal de Banque', 'type' => 'bank'],
            ['code' => 'CA', 'name' => 'Journal de Caisse', 'type' => 'cash'],
            ['code' => 'OD', 'name' => 'Opérations Diverses', 'type' => 'misc'],
            ['code' => 'PA', 'name' => 'Journal de Paie', 'type' => 'payroll'],
        ];

        foreach ($journals as $j) {
            DB::table('journals')->insert(array_merge($j, [
                'company_id' => $company->id,
                'next_number' => 1,
                'is_active' => true,
                'requires_attachment' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Départements
        $depts = [
            ['code' => 'DG', 'name' => 'Direction Générale'],
            ['code' => 'CF', 'name' => 'Comptabilité & Finance'],
            ['code' => 'RH', 'name' => 'Ressources Humaines'],
            ['code' => 'CM', 'name' => 'Commercial & Marketing'],
            ['code' => 'IT', 'name' => 'Informatique & Digital'],
        ];

        foreach ($depts as $d) {
            DB::table('departments')->insert(array_merge($d, [
                'company_id' => $company->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 4. Postes par défaut
        $positions = [
            ['code' => 'DIR-01', 'name' => 'Directeur Général'],
            ['code' => 'CMP-01', 'name' => 'Comptable'],
            ['code' => 'RH-01', 'name' => 'Responsable RH'],
            ['code' => 'COM-01', 'name' => 'Commercial'],
        ];

        foreach ($positions as $p) {
            DB::table('positions')->insert(array_merge($p, [
                'company_id' => $company->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 5. Cotisations sociales CNPS (si globales absentes)
        $existing = DB::table('social_contributions')->count();
        if ($existing === 0) {
            $contribs = [
                ['code' => 'CNPS_RET', 'name' => 'CNPS - Retraite', 'emp' => 4.80, 'er' => 7.70],
                ['code' => 'CNPS_PF', 'name' => 'CNPS - Prestations Familiales', 'emp' => 0, 'er' => 5.00],
                ['code' => 'CNPS_AT', 'name' => 'CNPS - Accidents de Travail', 'emp' => 0, 'er' => 2.00],
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
                    'employee_rate' => $c['emp'],
                    'employer_rate' => $c['er'],
                    'ceiling' => 500000,
                    'effective_from' => '2020-01-01',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 6. TVA 18%
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

        DB::table('tax_rates')->insert([
            'tax_id' => DB::getPdo()->lastInsertId(),
            'rate' => 18.00,
            'effective_from' => '2020-01-01',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 7. Paramètres par défaut
        $settings = [
            ['key' => 'language', 'value' => 'fr', 'group' => 'general'],
            ['key' => 'timezone', 'value' => $company->timezone, 'group' => 'general'],
            ['key' => 'invoice_payment_days', 'value' => '30', 'group' => 'general'],
        ];

        foreach ($settings as $s) {
            DB::table('settings')->insert(array_merge($s, [
                'company_id' => $company->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
