<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\Leave;
use Illuminate\Database\Seeder;

class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();
        if (! $company) {
            return;
        }

        $employees = [
            ['first_name' => 'Awa', 'last_name' => 'Koné', 'email' => 'awa@fiducia-africa.local', 'matricule' => 'EMP-2026-0001', 'status' => 'active', 'department_id' => 1, 'position_id' => 1],
            ['first_name' => 'Moussa', 'last_name' => 'Traoré', 'email' => 'moussa@fiducia-africa.local', 'matricule' => 'EMP-2026-0002', 'status' => 'active', 'department_id' => 2, 'position_id' => 2],
            ['first_name' => 'Fatou', 'last_name' => 'Diabaté', 'email' => 'fatou@fiducia-africa.local', 'matricule' => 'EMP-2026-0003', 'status' => 'active', 'department_id' => 3, 'position_id' => 3],
        ];

        foreach ($employees as $data) {
            Employee::firstOrCreate(
                ['company_id' => $company->id, 'matricule' => $data['matricule']],
                [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'sex' => 'F',
                    'hire_date' => now()->subMonths(6)->toDateString(),
                    'department_id' => $data['department_id'],
                    'position_id' => $data['position_id'],
                    'status' => $data['status'],
                    'payment_method' => 'bank',
                    'bank_name' => 'SGBCI',
                    'bank_account' => '0000000001',
                ]
            );
        }

        $employee = Employee::where('company_id', $company->id)->first();
        if ($employee) {
            Leave::firstOrCreate(
                ['company_id' => $company->id, 'employee_id' => $employee->id, 'start_date' => now()->subDays(5)->toDateString()],
                [
                    'leave_type' => 'annual',
                    'end_date' => now()->subDays(1)->toDateString(),
                    'days_count' => 5,
                    'reason' => 'Congé annuel',
                    'status' => 'pending',
                ]
            );
        }
    }
}
