<?php

namespace App\Modules\Hr\Database\Seeders;

use App\Models\Company;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Position;
use Illuminate\Database\Seeder;

class HrBaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            return;
        }

        $contractTypes = [
            ['name' => 'CDI', 'code' => 'CDI'],
            ['name' => 'CDD', 'code' => 'CDD'],
            ['name' => 'Stage', 'code' => 'STAGE'],
            ['name' => 'Période d\'essai', 'code' => 'ESSAI'],
            ['name' => 'Contrat saisonnier', 'code' => 'SAISON'],
        ];

        foreach ($contractTypes as $type) {
            ContractType::firstOrCreate([
                'name' => $type['name'],
            ], [
                'code' => $type['code'],
                'is_active' => true,
            ]);
        }

        $departments = [
            ['code' => 'DIR', 'name' => 'Direction'],
            ['code' => 'COMPTA', 'name' => 'Comptabilité'],
            ['code' => 'RH', 'name' => 'Ressources Humaines'],
            ['code' => 'COMM', 'name' => 'Commercial'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate([
                'company_id' => $company->id,
                'code' => $department['code'],
            ], [
                'name' => $department['name'],
                'is_active' => true,
            ]);
        }

        $positions = [
            ['code' => 'DG', 'name' => 'Directeur Général', 'department' => 'DIR'],
            ['code' => 'COMPTABLE', 'name' => 'Comptable', 'department' => 'COMPTA'],
            ['code' => 'RESP-RH', 'name' => 'Responsable RH', 'department' => 'RH'],
            ['code' => 'ASSISTANT', 'name' => 'Assistant administratif', 'department' => 'DIR'],
        ];

        foreach ($positions as $position) {
            $department = Department::where('company_id', $company->id)
                ->where('code', $position['department'])
                ->first();

            Position::firstOrCreate([
                'company_id' => $company->id,
                'code' => $position['code'],
            ], [
                'name' => $position['name'],
                'department_id' => $department?->id,
                'is_active' => true,
            ]);
        }
    }
}
