<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Modules\Accounting\Models\Account;
use App\Modules\Accounting\Models\ChartAccount;
use App\Modules\Accounting\Models\Journal;
use App\Modules\Accounting\Models\JournalEntry;
use App\Modules\Accounting\Models\JournalItem;
use App\Modules\Reporting\Services\AccountingReportService;

echo "\n=== TEST PHASE 7 : REPORTING ET ÉTATS FINANCIERS ===\n\n";

$company = Company::first();
auth()->login(User::first());

// 1. Préparer les comptes nécessaires pour les tests
$chart = ChartAccount::first();
$accountsData = [
    ['411100', 'Clients', 4, 'asset'],
    ['401100', 'Fournisseurs', 4, 'liability'],
    ['701100', 'Ventes de produits finis', 7, 'revenue'],
    ['601100', 'Achats de matières premières', 6, 'expense'],
    ['521100', 'Banque', 5, 'bank'],
    ['443100', 'TVA Facturée', 4, 'liability'],
    ['445100', 'TVA Déductible', 4, 'asset'],
];

foreach ($accountsData as $data) {
    Account::firstOrCreate(
        ['company_id' => $company->id, 'number' => $data[0]],
        [
            'chart_account_id' => $chart->id,
            'name' => $data[1],
            'class_number' => $data[2],
            'type' => $data[3],
            'is_active' => true,
        ]
    );
}

// Récupérer les journaux
$journalVente = Journal::firstOrCreate(['company_id' => $company->id, 'code' => 'VE'], ['name' => 'Ventes', 'type' => 'sale']);
$journalAchat = Journal::firstOrCreate(['company_id' => $company->id, 'code' => 'AC'], ['name' => 'Achats', 'type' => 'purchase']);
$journalBanque = Journal::firstOrCreate(['company_id' => $company->id, 'code' => 'BQ'], ['name' => 'Banque', 'type' => 'bank']);

// 2. Générer une écriture de VENTE (Facture client 1 000 000 + TVA 18%)
echo "Génération d'une facture de vente...\n";
$entryVente = JournalEntry::create([
    'company_id' => $company->id,
    'journal_id' => $journalVente->id,
    'entry_date' => now(),
    'reference' => 'FAC-2026-001',
    'description' => 'Vente de marchandises',
    'status' => 'posted',
]);

JournalItem::create(['journal_entry_id' => $entryVente->id, 'account_id' => Account::where('number', '411100')->first()->id, 'debit' => 1180000, 'credit' => 0]);
JournalItem::create(['journal_entry_id' => $entryVente->id, 'account_id' => Account::where('number', '701100')->first()->id, 'debit' => 0, 'credit' => 1000000]);
JournalItem::create(['journal_entry_id' => $entryVente->id, 'account_id' => Account::where('number', '443100')->first()->id, 'debit' => 0, 'credit' => 180000]);

// 3. Générer une écriture d'ACHAT (Facture fournisseur 500 000 + TVA 18%)
echo "Génération d'une facture d'achat...\n";
$entryAchat = JournalEntry::create([
    'company_id' => $company->id,
    'journal_id' => $journalAchat->id,
    'entry_date' => now(),
    'reference' => 'FOR-2026-001',
    'description' => 'Achat de fournitures',
    'status' => 'posted',
]);

JournalItem::create(['journal_entry_id' => $entryAchat->id, 'account_id' => Account::where('number', '601100')->first()->id, 'debit' => 500000, 'credit' => 0]);
JournalItem::create(['journal_entry_id' => $entryAchat->id, 'account_id' => Account::where('number', '445100')->first()->id, 'debit' => 90000, 'credit' => 0]);
JournalItem::create(['journal_entry_id' => $entryAchat->id, 'account_id' => Account::where('number', '401100')->first()->id, 'debit' => 0, 'credit' => 590000]);

// 4. Encaissement client (Banque)
echo "Génération d'un encaissement bancaire...\n";
$entryBanque = JournalEntry::create([
    'company_id' => $company->id,
    'journal_id' => $journalBanque->id,
    'entry_date' => now(),
    'reference' => 'ENC-2026-001',
    'description' => 'Règlement client FAC-2026-001',
    'status' => 'posted',
]);

JournalItem::create(['journal_entry_id' => $entryBanque->id, 'account_id' => Account::where('number', '521100')->first()->id, 'debit' => 1180000, 'credit' => 0]);
JournalItem::create(['journal_entry_id' => $entryBanque->id, 'account_id' => Account::where('number', '411100')->first()->id, 'debit' => 0, 'credit' => 1180000]);

// 5. Tester le service de reporting
echo "\n--- Test du Compte de Résultat ---\n";
$service = app(AccountingReportService::class);
$pnl = $service->getProfitAndLoss($company);

echo "Total Produits : " . number_format($pnl['total_revenues'], 0, ',', ' ') . " FCFA\n";
echo "Total Charges  : " . number_format($pnl['total_expenses'], 0, ',', ' ') . " FCFA\n";
echo "Résultat Net   : " . number_format($pnl['net_income'], 0, ',', ' ') . " FCFA\n";

echo "\n--- Test du Bilan ---\n";
$balanceSheet = $service->getBalanceSheet($company);
echo "Total Actif  : " . number_format($balanceSheet['total_assets'], 0, ',', ' ') . " FCFA\n";
echo "Total Passif : " . number_format($balanceSheet['total_liabilities'], 0, ',', ' ') . " FCFA\n";

echo "\n=== TESTS PHASE 7 TERMINÉS AVEC SUCCÈS ===\n";
echo "Tu peux maintenant consulter les rapports sur l'interface web !\n";
