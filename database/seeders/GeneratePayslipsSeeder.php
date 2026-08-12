<?php

namespace Database\Seeders;

use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Services\PayrollEngine;
use Illuminate\Database\Seeder;

class GeneratePayslipsSeeder extends Seeder
{
    public function run(): void
    {
        $payRun = PayRun::where('status', '!=', 'locked')->orderByDesc('id')->first();

        if (!$payRun) {
            $this->command->warn('Aucune période de paie trouvée.');
            return;
        }

        try {
            $this->command->info("Génération des bulletins pour : {$payRun->name}");
            app(PayrollEngine::class)->calculatePayRun($payRun);

            $count = $payRun->payslips()->count();
            $this->command->info("✅ {$count} bulletin(s) généré(s).");
        } catch (\Exception $e) {
            $this->command->error('Erreur : ' . $e->getMessage());
        }
    }
}
