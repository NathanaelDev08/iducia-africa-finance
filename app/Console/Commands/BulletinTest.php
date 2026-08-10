<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BulletinTest extends Command
{
    protected $signature = 'bulletin:test {slip? : Numéro du bulletin}';
    protected $description = 'Générer un PDF de test du bulletin';

    public function handle(): int
    {
        $slip = $this->argument('slip');
        $query = \App\Modules\Payroll\Models\Payslip::query();
        $p = $slip ? $query->where('slip_number', $slip)->first() : $query->first();

        if (!$p) {
            $this->error('Aucun bulletin en base');
            return 1;
        }

        $p->load(['company', 'employee.department', 'employee.position', 'payRun', 'items']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip-pdf', ['payslip' => $p]);
        $pdf->setPaper('a4', 'portrait');
        $out = $pdf->output();

        $pages = substr_count($out, '/Type /Page') - substr_count($out, '/Type /Pages');
        $file = storage_path('bulletin_BUL_CORRIGE.pdf');
        file_put_contents($file, $out);

        $this->info("✅ PDF généré : storage/bulletin_BUL_CORRIGE.pdf");
        $this->info("   Bulletin : " . ($p->slip_number ?? $p->id));
        $this->info("   Pages : $pages");

        return 0;
    }
}
