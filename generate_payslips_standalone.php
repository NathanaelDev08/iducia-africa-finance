<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Services\PayrollEngine;

$payRun = PayRun::find(1);
if (!$payRun) {
    echo "Période non trouvée\n";
    exit(1);
}

echo "Génération des bulletins pour : " . $payRun->name . "\n";

try {
    app(PayrollEngine::class)->calculatePayRun($payRun);
    $count = $payRun->payslips()->count();
    echo "✅ $count bulletin(s) généré(s) avec succès.\n";
} catch (\Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}
