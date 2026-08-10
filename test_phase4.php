<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Modules\Hr\Models\ContractType;
use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Position;
use App\Modules\Hr\Services\EmployeeService;
use App\Modules\Hr\Services\Exceptions\DuplicateMatriculeException;

echo "\n=== TEST PHASE 4 : RH DE BASE ===\n\n";

$company = Company::first();
$user = User::first();

if (!$company || !$user) {
    echo "ERREUR : entreprise ou utilisateur introuvable. Lance migrate + seed avant.\n";
    exit(1);
}

auth()->login($user);

$service = app(EmployeeService::class);

// Département de test
$department = Department::firstOrCreate([
    'company_id' => $company->id,
    'code' => 'TEST-DEPT',
], [
    'name' => 'Département Test',
    'is_active' => true,
]);

// Poste de test
$position = Position::firstOrCreate([
    'company_id' => $company->id,
    'code' => 'TEST-POSTE',
], [
    'name' => 'Poste Test',
    'department_id' => $department->id,
    'is_active' => true,
]);

// Type de contrat
$contractType = ContractType::firstOrCreate([
    'name' => 'CDI',
], [
    'code' => 'CDI',
    'is_active' => true,
]);

echo "--- TEST 1 : Création d'un employé ---\n";

$email = 'employe.test+' . time() . '@fiducia-africa.local';

$employee = $service->createEmployee($company, [
    'last_name' => 'KOUASSI',
    'first_name' => 'Aya',
    'email' => $email,
    'sex' => 'F',
    'nationality' => 'Ivoirienne',
    'hire_date' => now()->subMonths(14)->toDateString(),
    'department_id' => $department->id,
    'position_id' => $position->id,
    'payment_method' => 'bank',
    'payment_currency' => 'XOF',
]);

echo "✓ Employé créé : {$employee->matricule} - {$employee->full_name}\n";
echo "  Ancienneté : {$employee->seniority_years} an(s)\n";
echo "  Département : {$employee->department->name}\n";
echo "  Poste : {$employee->position->name}\n\n";

echo "--- TEST 2 : Ajout d'un contrat ---\n";

$contract = $service->addContract($employee, [
    'contract_type_id' => $contractType->id,
    'contract_number' => 'CTR-' . now()->format('Y') . '-001',
    'start_date' => $employee->hire_date->toDateString(),
    'working_hours_per_week' => 40,
    'base_salary' => 750000,
    'status' => 'active',
]);

echo "✓ Contrat ajouté : {$contract->contract_number}\n";
echo "  Type : {$contract->contractType->name}\n";
echo "  Salaire de base : {$contract->base_salary} FCFA\n\n";

echo "--- TEST 3 : Ajout d'un document ---\n";

$document = $service->addDocument($employee, [
    'document_type' => 'id_card',
    'name' => 'Carte nationale d\'identité',
    'issued_at' => now()->subYear()->toDateString(),
    'expires_at' => now()->addYears(5)->toDateString(),
    'status' => 'valid',
]);

echo "✓ Document ajouté : {$document->name}\n";
echo "  Type : {$document->document_type}\n\n";

echo "--- TEST 4 : Refus d'un matricule dupliqué ---\n";

try {
    $service->createEmployee($company, [
        'matricule' => $employee->matricule,
        'last_name' => 'DUPONT',
        'first_name' => 'Jean',
        'hire_date' => now()->toDateString(),
    ]);

    echo "✗ ERREUR : le matricule dupliqué a été accepté !\n\n";
} catch (DuplicateMatriculeException $e) {
    echo "✓ REFUSÉ comme prévu : {$e->getMessage()}\n\n";
}

echo "--- TEST 5 : Vérification des relations ---\n";

$contractsCount = $employee->contracts()->count();
$documentsCount = $employee->documents()->count();

echo "✓ Nombre de contrats : {$contractsCount}\n";
echo "✓ Nombre de documents : {$documentsCount}\n\n";

echo "--- TEST 6 : Audit log RH ---\n";

$logsCount = \Spatie\Activitylog\Models\Activity::where('caused_by', $user->id)->count();

echo "✓ Nombre d'entrées audit log : {$logsCount}\n\n";

echo "=== TESTS PHASE 4 TERMINÉS AVEC SUCCÈS ===\n";
