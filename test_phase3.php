<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\Period;
use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Accounting\Services\EntryService;
use App\Modules\Accounting\Services\Exceptions\UnbalancedEntryException;
use App\Modules\Accounting\Services\Exceptions\EntryLockedException;

echo "\n=== TEST PHASE 3 : COMPTABILITÉ DE BASE ===\n\n";

$company = Company::first();
$user = User::first();

if (!$company || !$user) {
    echo "ERREUR : Aucune entreprise ou utilisateur trouvé. Lance 'php artisan migrate:fresh --seed' d'abord.\n";
    exit(1);
}

// Simulation d'authentification
auth()->login($user);

$service = app(EntryService::class);

// Récupérer les comptes nécessaires
$bankAccount = Account::where('company_id', $company->id)->where('number', '521100')->first(); 
$capitalAccount = Account::where('company_id', $company->id)->where('number', '101300')->first(); 
$journalOD = Journal::where('company_id', $company->id)->where('code', 'OD')->first();
$period = Period::where('company_id', $company->id)->where('number', now()->month)->first();

if (!$bankAccount || !$capitalAccount || !$journalOD || !$period) {
    echo "ERREUR : Référentiels comptables manquants. Le seeder SYSCOHADA a-t-il bien tourné ?\n";
    exit(1);
}

echo "Entreprise : {$company->name}\n";
echo "Compte banque : {$bankAccount->number} - {$bankAccount->name}\n";
echo "Compte capital : {$capitalAccount->number} - {$capitalAccount->name}\n";
echo "Journal : {$journalOD->code} - {$journalOD->name}\n";
echo "Période : {$period->name}\n\n";

// --- TEST 1 : Création d'une écriture équilibrée ---
echo "--- TEST 1 : Création écriture équilibrée (apport en capital 10.000.000 FCFA) ---\n";
$entry = $service->createDraft($company, [
    'journal_id' => $journalOD->id,
    'period_id' => $period->id,
    'reference' => 'CAP-2026-001',
    'entry_date' => now()->toDateString(),
    'description' => 'Apport initial en capital',
], [
    [
        'account_id' => $bankAccount->id,
        'description' => 'Virement capital',
        'debit' => 10000000,
        'credit' => 0,
    ],
    [
        'account_id' => $capitalAccount->id,
        'description' => 'Capital souscrit versé',
        'debit' => 0,
        'credit' => 10000000,
    ],
]);

echo "✓ Écriture créée : ID={$entry->id}, Statut={$entry->status}\n";
echo "  Débit total : {$entry->total_debit}\n";
echo "  Crédit total : {$entry->total_credit}\n";
echo "  Équilibrée : " . ($entry->isBalanced() ? 'OUI' : 'NON') . "\n\n";

// --- TEST 2 : Validation de l'écriture équilibrée ---
echo "--- TEST 2 : Validation de l'écriture ---\n";
try {
    $service->validate($entry, $user);
    $freshEntry = $entry->fresh();
    echo "✓ Écriture validée avec succès : {$freshEntry->entry_number}\n";
    echo "  Statut : {$freshEntry->status}\n";
    echo "  Verrouillée : " . ($freshEntry->is_locked ? 'OUI' : 'NON') . "\n\n";
} catch (\Exception $e) {
    echo "✗ ERREUR : {$e->getMessage()}\n\n";
}

// --- TEST 3 : Tentative de validation d'une écriture déjà validée (doit échouer) ---
echo "--- TEST 3 : Tentative de validation d'une écriture DÉJÀ validée ---\n";
try {
    $service->validate($entry, $user);
    echo "✗ ERREUR : L'écriture a pu être validée deux fois !\n\n";
} catch (EntryLockedException $e) {
    echo "✓ BLOQUÉ comme prévu : {$e->getMessage()}\n\n";
}

// --- TEST 4 : Tentative de validation d'une écriture déséquilibrée (doit échouer) ---
echo "--- TEST 4 : Tentative de validation d'une écriture DÉSÉQUILIBRÉE ---\n";
$badEntry = $service->createDraft($company, [
    'journal_id' => $journalOD->id,
    'period_id' => $period->id,
    'reference' => 'BAD-001',
    'entry_date' => now()->toDateString(),
    'description' => 'Écriture volontairement fausse',
], [
    ['account_id' => $bankAccount->id, 'debit' => 100000, 'credit' => 0],
    ['account_id' => $capitalAccount->id, 'debit' => 0, 'credit' => 90000], // Manque 10.000
]);
try {
    $service->validate($badEntry, $user);
    echo "✗ ERREUR : L'écriture déséquilibrée a été validée !\n\n";
} catch (UnbalancedEntryException $e) {
    echo "✓ REFUSÉ comme prévu : {$e->getMessage()}\n\n";
}

// --- TEST 5 : Contre-passation (extourne) ---
echo "--- TEST 5 : Contre-passation de l'écriture validée ---\n";
try {
    $reversal = $service->reverse($entry, $user, 'Erreur de montant saisie');
    echo "✓ Contre-passation créée : {$reversal->entry_number}\n";
    echo "  Statut : {$reversal->status}\n";
    echo "  Référence : {$reversal->reference}\n";
    echo "  Description : {$reversal->description}\n";
    
    $original = $entry->fresh();
    echo "\n  Écriture originale : Statut={$original->status}\n";
    echo "  Contre-passée par : ID {$original->reversed_by_id}\n\n";
} catch (\Exception $e) {
    echo "✗ ERREUR : {$e->getMessage()}\n\n";
}

// --- TEST 6 : Vérification de l'audit log ---
echo "--- TEST 6 : Vérification audit log ---\n";
$logsCount = \Spatie\Activitylog\Models\Activity::where('subject_type', AccountingEntry::class)->count();
echo "✓ Nombre d'entrées dans l'audit log pour les écritures : {$logsCount}\n\n";

echo "=== TESTS TERMINÉS AVEC SUCCÈS ===\n";
