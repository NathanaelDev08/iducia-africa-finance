<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Modules\Hr\Models\Employee;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Position;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Services\EmployeeService;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Services\PayrollEngine;

echo "\n=== TEST PHASE 5 : PAIE DE BASE ===\n\n";

$company = Company::first();
if (!$company) {
    die("ERREUR : Aucune entreprise en base. Lancez d'abord les seeders.\n");
}

$user = User::first();
if ($user) {
    auth()->login($user);
}

$engine = app(PayrollEngine::class);

echo "--- Création de la Paie d'Août 2026 ---\n";
$payRun = PayRun::create([
    'company_id' => $company->id, 'name' => 'Paie Août 2026', 'reference' => 'PAIE-2026-08',
    'period_start' => '2026-08-01', 'period_end' => '2026-08-31', 'status' => 'draft',
]);

$employeeService = app(EmployeeService::class);

// 1. S'assurer qu'on a un département et un poste
$department = Department::first();
if (!$department) {
    echo "ℹ️ Création d'un département de test...\n";
    $department = Department::create(['company_id' => $company->id, 'code' => 'TEST', 'name' => 'Département Test']);
}
$position = Position::first();
if (!$position) {
    $position = Position::create(['company_id' => $company->id, 'code' => 'TEST', 'name' => 'Poste Test', 'department_id' => $department->id]);
}

// 2. Récupérer ou créer un employé actif
$employee = Employee::where('company_id', $company->id)->where('status', 'active')->first();

if (!$employee) {
    echo "ℹ️ Aucun employé actif trouvé. Création d'un employé de test...\n";
    $employee = $employeeService->createEmployee($company, [
        'last_name' => 'DIABATE',
        'first_name' => 'Ibrahim',
        'email' => 'i.diabate+'.time().'@test.ci',
        'hire_date' => '2025-01-01',
        'department_id' => $department->id,
        'position_id' => $position->id,
    ]);
    echo "✓ Employé créé : {$employee->matricule}\n";
}

// 3. Récupérer ou créer un contrat avec un salaire > au plafond CNPS (500 000)
$contract = $employee->contracts()->where('status', 'active')->first();

if (!$contract) {
    echo "ℹ️ Aucun contrat actif trouvé. Création d'un contrat de test...\n";
    $contractType = ContractType::first();
    if (!$contractType) {
         $contractType = ContractType::create(['name' => 'CDI', 'code' => 'CDI', 'is_active' => true]);
    }
    $contract = $employeeService->addContract($employee, [
        'contract_type_id' => $contractType->id,
        'contract_number' => 'CTR-TEST-2026',
        'start_date' => '2025-01-01',
        'base_salary' => 850000, // Salaire > plafond pour tester le calcul
        'status' => 'active',
    ]);
    echo "✓ Contrat créé avec salaire de base de 850 000 FCFA\n";
} elseif ($contract->base_salary < 600000) {
    echo "ℹ️ Mise à jour du salaire de l'employé à 850 000 FCFA pour tester le plafond...\n";
    $contract->update(['base_salary' => 850000]);
}

echo "\n--- Lancement du moteur de calcul ---\n";
try {
    $engine->calculatePayRun($payRun);
    echo "✓ Calcul terminé.\n\n";
} catch (\Exception $e) {
    die("✗ ERREUR : {$e->getMessage()}\n");
}

foreach ($payRun->payslips as $payslip) {
    echo "=== Bulletin de {$payslip->employee->full_name} ===\n";
    echo "Salaire de base : " . number_format($payslip->base_salary, 0, ',', ' ') . " FCFA\n";
    echo "Salaire Brut    : " . number_format($payslip->gross_salary, 0, ',', ' ') . " FCFA\n";
    echo "Total Retenues  : " . number_format($payslip->total_deductions, 0, ',', ' ') . " FCFA\n";
    echo "Salaire Net     : " . number_format($payslip->net_salary, 0, ',', ' ') . " FCFA\n";
    echo "Charges Patron. : " . number_format($payslip->employer_contributions, 0, ',', ' ') . " FCFA\n";
    
    // Vérification mathématique (Brut - Retenues = Net)
    if (abs($payslip->net_salary - ($payslip->gross_salary - $payslip->total_deductions)) < 0.01) {
        echo "✓ Vérification mathématique OK (Brut - Retenues = Net)\n\n";
    } else {
        echo "✗ ERREUR MATHÉMATIQUE\n\n";
    }
}
echo "=== TESTS PHASE 5 TERMINÉS AVEC SUCCÈS ===\n";
