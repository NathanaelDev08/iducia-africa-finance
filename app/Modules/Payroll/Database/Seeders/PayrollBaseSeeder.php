<?php
namespace App\Modules\Payroll\Database\Seeders;
use App\Modules\Payroll\Models\SocialContribution;
use Illuminate\Database\Seeder;

class PayrollBaseSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['code' => 'CNPS_RET', 'name' => 'CNPS - Retraite', 'emp' => 4.8, 'er' => 7.7],
            ['code' => 'CNPS_PREST', 'name' => 'CNPS - Prestations Familiales', 'emp' => 0.0, 'er' => 5.0],
            ['code' => 'CNPS_AT', 'name' => 'CNPS - Accidents de Travail', 'emp' => 0.0, 'er' => 2.0],
        ];

        foreach ($data as $d) {
            $cnps = SocialContribution::firstOrCreate(
                ['code' => $d['code']],
                ['name' => $d['name'], 'organism' => 'CNPS', 'is_active' => true]
            );
            $cnps->rates()->create([
                'employee_rate' => $d['emp'], 'employer_rate' => $d['er'],
                'ceiling' => 500000.00, 'effective_from' => '2020-01-01', 'is_active' => true,
            ]);
        }
    }
}
