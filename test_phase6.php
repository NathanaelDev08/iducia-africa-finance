<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Services\PayrollAccountingService;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\Account;

echo "\n=== TEST PHASE 6 : INTÉGRATION COMPTABLE ===\n\n";

$company = Company::first();
auth()->login(User::first());

$payRun = PayRun::where('company_id', $company->id)->latest()->first();

if (!$payRun) {
    echo "Aucune période de paie trouvée. Veuillez lancer test_phase5.php d'abord.\n";
    exit(1);
}

echo "Période trouvée : {$payRun->name} (Statut: {$payRun->status})\n";

// 1. S'assurer qu'un plan comptable existe
$chartAccount = ChartAccount::where('company_id', $company->id)->first();
if (!$chartAccount) {
    echo "⚠️ Plan comptable introuvable. Création du plan SYSCOHADA...\n";
    $chartAccount = ChartAccount::create([
        'company_id' => $company->id,
        'name' => 'Plan SYSCOHADA Standard',
        'slug' => 'syscohada-' . $company->id,
        'standard' => 'SYSCOHADA',
        'is_default' => true,
        'is_active' => true,
    ]);
}

// 2. S'assurer que les comptes comptables existent
$accountsToCheck = [
    ['661100', 'Rémunérations du personnel', 6, 'expense'],
    ['664100', 'Charges sociales patronales', 6, 'expense'],
    ['421100', 'Personnel - Rémunérations dues', 4, 'liability'],
    ['433100', 'CNPS et autres organismes sociaux', 4, 'liability'],
    ['442100', 'État - Impôts sur salaires', 4, 'liability'],
];

foreach ($accountsToCheck as $accData) {
    [$number, $name, $class, $type] = $accData;
    
    $account = Account::where('company_id', $company->id)
        ->where('number', $number)
        ->first();
        
    if (!$account) {
        echo "⚠️ Compte {$number} introuvable. Création...\n";
        Account::create([
            'company_id' => $company->id,
            'chart_account_id' => $chartAccount->id,
            'number' => $number,
            'name' => $name,
            'class_number' => $class,
            'type' => $type,
            'is_active' => true,
            'is_reconcilable' => false,
            'is_auxiliary' => false,
        ]);
    }
}

// 3. S'assurer qu'un journal de type "Paie" ou "OD" existe
$journal = \App\Modules\Accounting\Models\Journal::where('company_id', $company->id)->where('type', 'payroll')->first();
if (!$journal) {
    echo "⚠️ Journal de Paie introuvable. Création...\n";
    $journal = \App\Modules\Accounting\Models\Journal::create([
        'company_id' => $company->id,
        'code' => 'PA',
        'name' => 'Journal de Paie',
        'type' => 'payroll',
        'is_active' => true,
    ]);
}

echo "--- Lancement de la comptabilisation ---\n";

try {
    $service = app(PayrollAccountingService::class);
    $journalEntry = $service->postPayRunToAccounting($payRun);
    
    echo "✓ Écriture comptable générée !\n";
    echo "  Référence : {$journalEntry->reference}\n";
    echo "  Montant Total : " . number_format($journalEntry->total_debit, 0, ',', ' ') . " FCFA\n";
    
    // Vérification équilibre
    if (abs($journalEntry->total_debit - $journalEntry->total_credit) < 0.01) {
        echo "✓ Écriture ÉQUILIBRÉE (Débit = Crédit)\n";
    } else {
        echo "✗ ERREUR : Écriture déséquilibrée !\n";
    }

    echo "\n=== TESTS PHASE 6 TERMINÉS AVEC SUCCÈS ===\n";
    
} catch (\Exception $e) {
    echo "✗ ERREUR : {$e->getMessage()}\n";
}
