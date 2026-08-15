<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'max_users' => 5,
                'max_employees' => 20,
                'modules' => ['dashboard', 'hr', 'payroll', 'accounting', 'reports'],
                'price' => 0,
                'trial_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Professionnel',
                'slug' => 'professionnel',
                'max_users' => 20,
                'max_employees' => 100,
                'modules' => ['dashboard', 'hr', 'payroll', 'accounting', 'sales', 'purchasing', 'inventory', 'treasury', 'cash', 'tax', 'assets', 'reports'],
                'price' => 49000,
                'trial_days' => 14,
                'is_active' => true,
            ],
            [
                'name' => 'Entreprise',
                'slug' => 'entreprise',
                'max_users' => 100000, // illimité en pratique
                'max_employees' => 100000,
                'modules' => ['dashboard', 'hr', 'payroll', 'accounting', 'sales', 'purchasing', 'inventory', 'treasury', 'cash', 'tax', 'assets', 'reports', 'settings'],
                'price' => 149000,
                'trial_days' => 30,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
