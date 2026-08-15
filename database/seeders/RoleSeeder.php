<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super-admin',
            'admin-company',
            'accountant',
            'hr-manager',
            'payroll-manager',
            'tax-manager',
            'auditor',
            'manager',
            'employee',
            'commercial',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }
}
