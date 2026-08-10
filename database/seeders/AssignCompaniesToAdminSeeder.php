<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AssignCompaniesToAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer 2 entreprises si la table est vide
        if (Company::count() === 0) {
            $cols = Schema::getColumnListing('companies');

            $make = function (string $name, string $slug, string $tax) use ($cols) {
                $data = [];
                if (in_array('name', $cols))       $data['name'] = $name;
                if (in_array('slug', $cols))       $data['slug'] = $slug;
                if (in_array('tax_number', $cols)) $data['tax_number'] = $tax;
                if (in_array('currency', $cols))   $data['currency'] = 'XOF';
                if (in_array('fiscal_year_start_month', $cols)) $data['fiscal_year_start_month'] = 1;
                return Company::create($data);
            };

            $make('FIDUCIA AFRICA CONSEIL & FINANCE', 'fiducia-africa', 'CI-2024-001');
            $make('FIDUCIA CONSULTING', 'fiducia-consulting', 'CI-2024-002');

            $this->command->info('✓ 2 entreprises créées.');
        }

        // 2. Associer TOUS les utilisateurs à leurs entreprises autorisées
        $companies = Company::all();

        foreach (User::all() as $user) {
            foreach ($companies as $company) {
                try {
                    $user->companies()->syncWithoutDetaching([$company->id => ['role' => 'admin']]);
                } catch (\Throwable $e) {
                    $user->companies()->syncWithoutDetaching([$company->id]);
                }
            }
            $this->command->info("✓ {$user->name} ({$user->email}) → " . $user->companies()->count() . ' entreprise(s)');
        }
    }
}
