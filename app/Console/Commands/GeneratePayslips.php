<?php

namespace App\Console\Commands;

use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Services\PayrollEngine;
use Illuminate\Console\Command;

class GeneratePayslips extends Command
{
    protected $signature = 'payroll:generate {payRun? : ID de la période}';
    protected $description = 'Générer les bulletins de paie pour une période';

    public function handle(): int
    {
        $payRunId = $this->argument('payRun');

        if ($payRunId) {
            $payRun = PayRun::find($payRunId);
            if (!$payRun) {
                $this->error("Période $payRunId introuvable.");
                return 1;
            }
        } else {
            $payRun = PayRun::where('status', '!=', 'locked')->orderByDesc('id')->first();
            if (!$payRun) {
                $this->error('Aucune période non-verrouillée trouvée.');
                return 1;
            }
        }

        try {
            $this->info("Génération des bulletins pour : {$payRun->name}");
            app(PayrollEngine::class)->calculatePayRun($payRun);

            $count = $payRun->payslips()->count();
            $this->info("✅ {$count} bulletin(s) généré(s) avec succès.");
            return 0;
        } catch (\Exception $e) {
            $this->error('Erreur : ' . $e->getMessage());
            return 1;
        }
    }
}
