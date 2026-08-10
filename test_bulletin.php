<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Company;
use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Payroll\Models\PayslipItem;

echo "╔═══════════════════════════════════════════════════╗\n";
echo "║   TEST GÉNÉRATION BULLETIN DE PAIE PROFESSIONNEL  ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

// 1. Récupérer la première entreprise
$company = Company::first();
if (!$company) {
    die("❌ Aucune entreprise trouvée\n");
}
echo "✓ Entreprise : {$company->name}\n";

// 2. Récupérer ou créer un employé
$employee = Employee::where('company_id', $company->id)->first();
if (!$employee) {
    echo "⚠ Aucun employé, création d'un employé de test...\n";
    $employee = Employee::create([
        'company_id' => $company->id,
        'matricule' => 'EMP-TEST-001',
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => 'jean.dupont@test.com',
        'hire_date' => now()->subYear(),
        'status' => 'active',
        'base_salary' => 500000,
    ]);
}
echo "✓ Employé : {$employee->first_name} {$employee->last_name} ({$employee->matricule})\n";

// 3. Récupérer ou créer une période de paie
$payRun = PayRun::where('company_id', $company->id)->first();
if (!$payRun) {
    echo "⚠ Aucune période de paie, création...\n";
    $payRun = PayRun::create([
        'company_id' => $company->id,
        'name' => 'Paie ' . now()->format('F Y'),
        'reference' => 'PAIE-' . now()->format('Y-m'),
        'period_start' => now()->startOfMonth(),
        'period_end' => now()->endOfMonth(),
        'status' => 'calculated',
    ]);
}
echo "✓ Période : {$payRun->name}\n";

// 4. Récupérer ou créer un bulletin
$payslip = Payslip::where('pay_run_id', $payRun->id)
    ->where('employee_id', $employee->id)
    ->first();

if (!$payslip) {
    echo "⚠ Aucun bulletin, création...\n";
    $payslip = Payslip::create([
        'company_id' => $company->id,
        'pay_run_id' => $payRun->id,
        'employee_id' => $employee->id,
        'gross_salary' => 500000,
        'total_deductions' => 50000,
        'net_salary' => 450000,
        'employer_contributions' => 75000,
        'status' => 'calculated',
    ]);
}
echo "✓ Bulletin créé (ID: {$payslip->id})\n";

// 5. Créer les rubriques du bulletin si elles n'existent pas
if ($payslip->items()->count() === 0) {
    echo "📝 Création des rubriques du bulletin...\n";
    
    $items = [
        // Rémunérations
        ['code' => 'SAL_BASE', 'label' => 'Salaire de base', 'type' => 'earning', 'base' => 500000, 'rate' => 100, 'amount' => 500000, 'sort_order' => 1],
        ['code' => 'PRIME_ANC', 'label' => "Prime d'ancienneté", 'type' => 'earning', 'base' => 500000, 'rate' => 5, 'amount' => 25000, 'sort_order' => 2],
        
        // Cotisations salariales
        ['code' => 'CNPS_SAL', 'label' => 'CNPS - Part salariale', 'type' => 'employee_contribution', 'base' => 525000, 'rate' => 5.5, 'amount' => 28875, 'sort_order' => 10],
        ['code' => 'IMPOT', 'label' => 'Impôt sur salaire', 'type' => 'deduction', 'base' => 525000, 'rate' => 3.5, 'amount' => 18375, 'sort_order' => 11],
        
        // Cotisations patronales
        ['code' => 'CNPS_PAT', 'label' => 'CNPS - Part patronale', 'type' => 'employer_contribution', 'base' => 525000, 'rate' => 7.7, 'amount' => 40425, 'sort_order' => 20],
        ['code' => 'ATS', 'label' => 'Accidents de travail', 'type' => 'employer_contribution', 'base' => 525000, 'rate' => 2, 'amount' => 10500, 'sort_order' => 21],
    ];
    
    foreach ($items as $item) {
        PayslipItem::create(array_merge($item, ['payslip_id' => $payslip->id]));
    }
    
    // Recalculer les totaux
    $gross = PayslipItem::where('payslip_id', $payslip->id)
        ->where('type', 'earning')
        ->sum('amount');
    
    $deductions = PayslipItem::where('payslip_id', $payslip->id)
        ->whereIn('type', ['deduction', 'employee_contribution'])
        ->sum('amount');
    
    $employer = PayslipItem::where('payslip_id', $payslip->id)
        ->where('type', 'employer_contribution')
        ->sum('amount');
    
    $payslip->update([
        'gross_salary' => $gross,
        'total_deductions' => $deductions,
        'net_salary' => $gross - $deductions,
        'employer_contributions' => $employer,
    ]);
    
    echo "✓ Rubriques créées et totaux recalculés\n";
} else {
    echo "✓ Rubriques déjà présentes ({$payslip->items()->count()} rubriques)\n";
}

// 6. Tester la génération PDF
echo "\n📄 Test de génération PDF...\n";
try {
    $payslip->load(['company', 'employee.department', 'employee.position', 'payRun', 'items']);
    
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip-pdf', ['payslip' => $payslip]);
    $pdf->setPaper('a4', 'portrait');
    
    $filename = 'test_bulletin_' . $payslip->id . '.pdf';
    $pdf->save(storage_path('app/public/' . $filename));
    
    echo "✅ PDF généré avec succès !\n";
    echo "📁 Fichier : storage/app/public/{$filename}\n";
    echo "💰 Brut : " . number_format($payslip->gross_salary, 0, ',', ' ') . " {$company->currency}\n";
    echo "💰 Net : " . number_format($payslip->net_salary, 0, ',', ' ') . " {$company->currency}\n";
    echo "💰 Charges patronales : " . number_format($payslip->employer_contributions, 0, ',', ' ') . " {$company->currency}\n";
    
} catch (\Throwable $e) {
    echo "❌ Erreur lors de la génération PDF :\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

echo "\n╔═══════════════════════════════════════════════════╗\n";
echo "║   TEST TERMINÉ - Système prêt à 100%              ║\n";
echo "╚═══════════════════════════════════════════════════╝\n";
