<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $seeders = [ RoleSeeder::class, AdminCompanySeeder::class ];
        $modules = [
            \App\Modules\Accounting\Database\Seeders\SyscohadaChartSeeder::class,
            \App\Modules\Hr\Database\Seeders\HrBaseSeeder::class,
            \App\Modules\Payroll\Database\Seeders\PayrollBaseSeeder::class,
        ];
        foreach ($modules as $m) { if (class_exists($m)) $seeders[] = $m; }
        $this->call($seeders);
    }
}
