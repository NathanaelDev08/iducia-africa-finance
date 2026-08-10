<?php

namespace App\Modules\Tax\Database\Seeders;

use App\Models\Company;
use App\Modules\Tax\Models\Tax;
use Illuminate\Database\Seeder;

class TaxBaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (!$company) return;

        $defs = [
            ['code' => 'TVA_18', 'name' => 'TVA 18%', 'type' => 'vat', 'rate' => 18],
            ['code' => 'TS', 'name' => 'Taxe sur salaires (CFP)', 'type' => 'other', 'rate' => 4.5],
            ['code' => 'IS', 'name' => 'Impôt sur les sociétés', 'type' => 'other', 'rate' => 27],
        ];

        foreach ($defs as $def) {
            $tax = Tax::firstOrCreate(
                ['company_id' => $company->id, 'code' => $def['code']],
                ['name' => $def['name'], 'type' => $def['type'], 'is_active' => true]
            );

            $tax->rates()->firstOrCreate(
                ['effective_from' => '2020-01-01'],
                ['rate' => $def['rate'], 'is_active' => true]
            );
        }
    }
}
