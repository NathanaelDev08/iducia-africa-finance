<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ErpDashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', ErpDashboardController::class)->name('dashboard');

    // Comptabilité
    Route::get('/comptabilite', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'ecritures'])->name('accounting.index');
    Route::get('/comptabilite/ecritures/nouvelle', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'createEcriture'])->name('accounting.ecritures.create');
    Route::post('/comptabilite/ecritures', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'storeEcriture'])->name('accounting.ecritures.store');
    Route::post('/comptabilite/ecritures/{entry}/valider', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'validateEcriture'])->name('accounting.ecritures.validate');
    Route::post('/comptabilite/ecritures/{entry}/contre-passer', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'reverseEcriture'])->name('accounting.ecritures.reverse');
    Route::get('/comptabilite/plan-comptable', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'planComptable'])->name('accounting.plan');
    Route::get('/comptabilite/journaux', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'journaux'])->name('accounting.journals');
    Route::get('/comptabilite/balance', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'balance'])->name('accounting.balance');
    Route::get('/comptabilite/grand-livre', [\App\Http\Controllers\Inertia\ComptabiliteController::class, 'grandLivre'])->name('accounting.grand-livre');

    // RH
    Route::get('/hr', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'hub'])->name('hr.index');
    Route::get('/rh/employes/nouveau', [\App\Http\Controllers\Inertia\RhController::class, 'createEmploye'])->name('hr.employes.create');
    Route::post('/rh/employes', [\App\Http\Controllers\Inertia\RhController::class, 'storeEmploye'])->name('hr.employes.store');
    Route::get('/rh/contrats', [\App\Http\Controllers\Inertia\RhController::class, 'contrats'])->name('hr.contrats');
    Route::get('/rh/departements', [\App\Http\Controllers\Inertia\RhController::class, 'departements'])->name('hr.departements');
    Route::get('/rh/conges', [\App\Http\Controllers\Inertia\RhController::class, 'conges'])->name('hr.conges');

    // Paie

    // Fiscalité
    Route::get('/fiscalite', [\App\Http\Controllers\Inertia\FiscaliteController::class, 'declarations'])->name('tax.index');
    Route::get('/fiscalite/echeancier', [\App\Http\Controllers\Inertia\FiscaliteController::class, 'echeancier'])->name('tax.echeancier');
    Route::get('/fiscalite/parametres', [\App\Http\Controllers\Inertia\FiscaliteController::class, 'parametres'])->name('tax.parametres');

    // Rapports
    Route::get('/rapports', [\App\Http\Controllers\Inertia\RapportsController::class, 'comptables'])->name('reports.index');
    Route::get('/rapports/rh', [\App\Http\Controllers\Inertia\RapportsController::class, 'rh'])->name('reports.rh');
    Route::get('/rapports/paie', [\App\Http\Controllers\Inertia\RapportsController::class, 'paie'])->name('reports.paie');
    Route::get('/rapports/fiscaux', [\App\Http\Controllers\Inertia\RapportsController::class, 'fiscaux'])->name('reports.fiscaux');

    // Routes Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';

// ===== États financiers (Reporting) =====
Route::middleware(['auth'])->prefix('rapports')->name('reporting.')->group(function () {
    Route::get('/', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'index'])->name('index');
    Route::get('/balance', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/resultat', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'profitAndLoss'])->name('profit-and-loss');
    Route::get('/bilan', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'balanceSheet'])->name('balance-sheet');
});

// ===== Exports CSV Reporting =====
Route::middleware(['auth'])->prefix('rapports')->name('reporting.')->group(function () {
    Route::get('/export/balance', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'exportTrialBalance'])->name('export-balance');
    Route::get('/export/resultat', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'exportProfitAndLoss'])->name('export-resultat');
    Route::get('/export/bilan', [\App\Modules\Reporting\Http\Controllers\ReportingController::class, 'exportBalanceSheet'])->name('export-bilan');
});

// ===== Module Paie (complet) =====
Route::middleware(['auth', 'set_active_company'])->prefix('paie')->name('payroll.')->group(function () {
    Route::get('/', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'index'])->name('index');
    Route::get('/create', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'create'])->name('create');
    Route::post('/', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'store'])->name('store');
    Route::get('/{payRun}', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'show'])->name('show')->where('payRun', '[0-9]+');
    Route::post('/{payRun}/calculate', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'calculate'])->name('calculate');
    Route::post('/{payRun}/validate', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'validateRun'])->name('validate');
    Route::post('/{payRun}/post', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'postToAccounting'])->name('post');
    Route::post('/{payRun}/lock', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'lock'])->name('lock');
});

// ===== Onglets Paie (données spécifiques) =====
Route::middleware(['auth', 'set_active_company'])->prefix('paie')->name('payroll.')->group(function () {
    Route::get('/bulletins', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'bulletins'])->name('bulletins');
    Route::get('/calculs', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'calculs'])->name('calculs');
    Route::get('/integration', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'integration'])->name('integration');
    Route::get('/rubriques', [\App\Http\Controllers\Inertia\PaieController::class, 'rubriques'])->name('rubriques');
    Route::get('/journal', [\App\Http\Controllers\Inertia\PaieController::class, 'journalPaie'])->name('journal');
});

// ===== CRUD Employés (RH) =====
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/employees', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'show'])->name('employees.show')->where('employee', '[0-9]+');
    Route::put('/employees/{employee}', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/employees/{employee}/deactivate', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'deactivate'])->name('employees.deactivate');
    Route::post('/employees/{employee}/activate', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'activate'])->name('employees.activate');
});

// ===== CRUD Référentiels RH =====
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/referentials', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'index'])->name('referentials.index');
    Route::post('/referentials/departments', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'storeDepartment'])->name('referentials.departments.store');
    Route::put('/referentials/departments/{department}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'updateDepartment'])->name('referentials.departments.update');
    Route::delete('/referentials/departments/{department}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'destroyDepartment'])->name('referentials.departments.destroy');
    Route::post('/referentials/positions', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'storePosition'])->name('referentials.positions.store');
    Route::put('/referentials/positions/{position}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'updatePosition'])->name('referentials.positions.update');
    Route::delete('/referentials/positions/{position}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'destroyPosition'])->name('referentials.positions.destroy');
    Route::post('/referentials/contract-types', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'storeContractType'])->name('referentials.contract-types.store');
    Route::put('/referentials/contract-types/{contractType}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'updateContractType'])->name('referentials.contract-types.update');
    Route::delete('/referentials/contract-types/{contractType}', [\App\Modules\Hr\Http\Controllers\ReferentialController::class, 'destroyContractType'])->name('referentials.contract-types.destroy');
});

// Route temporaire pour visualiser le nouveau CRUD Employés
Route::middleware(['auth'])->get('/hr/crud', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'index'])->name('hr.crud');

// Redirige l'ancienne URL /rh vers /hr
Route::redirect('/rh', '/hr');

// ===== CRUD Comptabilité =====
Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {
    Route::post('/accounts', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'storeAccount'])->name('accounts.store');
    Route::put('/accounts/{account}', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'updateAccount'])->name('accounts.update');
    Route::delete('/accounts/{account}', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'destroyAccount'])->name('accounts.destroy');
    Route::post('/journals', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'storeJournal'])->name('journals.store');
    Route::put('/journals/{journal}', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'updateJournal'])->name('journals.update');
    Route::delete('/journals/{journal}', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'destroyJournal'])->name('journals.destroy');
    Route::post('/fiscal-years', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'storeFiscalYear'])->name('fiscal-years.store');
    Route::post('/fiscal-years/{fiscalYear}/close', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'closeFiscalYear'])->name('fiscal-years.close');
    Route::post('/periods/{period}/close', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'closePeriod'])->name('periods.close');
    Route::post('/periods/{period}/reopen', [\App\Modules\Accounting\Http\Controllers\AccountingController::class, 'reopenPeriod'])->name('periods.reopen');
});

// ===== CRUD Contrats / Congés / Documents =====
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::post('/contracts', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'storeContract'])->name('contracts.store');
    Route::put('/contracts/{contract}', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'updateContract'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'destroyContract'])->name('contracts.destroy');
    Route::post('/leaves', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'storeLeave'])->name('leaves.store');
    Route::post('/leaves/{leave}/approve', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'approveLeave'])->name('leaves.approve');
    Route::post('/leaves/{leave}/reject', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'rejectLeave'])->name('leaves.reject');
    Route::delete('/leaves/{leave}', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'destroyLeave'])->name('leaves.destroy');
    Route::post('/documents', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'storeDocument'])->name('documents.store');
    Route::delete('/documents/{document}', [\App\Modules\Hr\Http\Controllers\HrCrudController::class, 'destroyDocument'])->name('documents.destroy');
});

// ===== Module Achats =====
Route::middleware(['auth'])->prefix('achats')->name('purchasing.')->group(function () {
    Route::get('/', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'index'])->name('index');
    Route::post('/suppliers', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'storeSupplier'])->name('suppliers.store');
    Route::put('/suppliers/{supplier}', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'updateSupplier'])->name('suppliers.update');
    Route::delete('/suppliers/{supplier}', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'destroySupplier'])->name('suppliers.destroy');
    Route::post('/orders', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'storeOrder'])->name('orders.store');
    Route::put('/orders/{order}/status', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'updateOrderStatus'])->name('orders.status');
    Route::delete('/orders/{order}', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'destroyOrder'])->name('orders.destroy');
    Route::post('/invoices', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'storeInvoice'])->name('invoices.store');
    Route::post('/invoices/{invoice}/post', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'postInvoice'])->name('invoices.post');
    Route::delete('/invoices/{invoice}', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'destroyInvoice'])->name('invoices.destroy');
    Route::post('/payments', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'storePayment'])->name('payments.store');
    Route::post('/payments/{payment}/post', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'postPayment'])->name('payments.post');
    Route::delete('/payments/{payment}', [\App\Modules\Purchasing\Http\Controllers\PurchasingController::class, 'destroyPayment'])->name('payments.destroy');
});

// ===== Module Ventes =====
Route::middleware(['auth'])->prefix('ventes')->name('sales.')->group(function () {
    Route::get('/', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'index'])->name('index');
    Route::post('/clients', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'storeClient'])->name('clients.store');
    Route::put('/clients/{client}', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'updateClient'])->name('clients.update');
    Route::delete('/clients/{client}', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'destroyClient'])->name('clients.destroy');
    Route::post('/orders', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'storeOrder'])->name('orders.store');
    Route::put('/orders/{order}/status', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'updateOrderStatus'])->name('orders.status');
    Route::delete('/orders/{order}', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'destroyOrder'])->name('orders.destroy');
    Route::post('/invoices', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'storeInvoice'])->name('invoices.store');
    Route::post('/invoices/{invoice}/post', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'postInvoice'])->name('invoices.post');
    Route::delete('/invoices/{invoice}', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'destroyInvoice'])->name('invoices.destroy');
    Route::post('/payments', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'storePayment'])->name('payments.store');
    Route::post('/payments/{payment}/post', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'postPayment'])->name('payments.post');
    Route::delete('/payments/{payment}', [\App\Modules\Sales\Http\Controllers\SalesController::class, 'destroyPayment'])->name('payments.destroy');
});

// ===== Module Immobilisations =====
Route::middleware(['auth'])->prefix('immobilisations')->name('assets.')->group(function () {
    Route::get('/', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'index'])->name('index');
    Route::post('/assets', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'storeAsset'])->name('store');
    Route::put('/assets/{asset}', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'updateAsset'])->name('update');
    Route::delete('/assets/{asset}', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'destroyAsset'])->name('destroy');
    Route::post('/generate', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'generate'])->name('generate');
    Route::post('/depreciations/{depreciation}/post', [\App\Modules\Assets\Http\Controllers\AssetsController::class, 'postDepreciation'])->name('post');
});

// ===== Module Stock & Inventaire =====
Route::middleware(['auth'])->prefix('stock')->name('inventory.')->group(function () {
    Route::get('/', [\App\Modules\Inventory\Http\Controllers\InventoryController::class, 'index'])->name('index');
    Route::post('/items', [\App\Modules\Inventory\Http\Controllers\InventoryController::class, 'storeItem'])->name('items.store');
    Route::put('/items/{item}', [\App\Modules\Inventory\Http\Controllers\InventoryController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}', [\App\Modules\Inventory\Http\Controllers\InventoryController::class, 'destroyItem'])->name('items.destroy');
    Route::post('/movements', [\App\Modules\Inventory\Http\Controllers\InventoryController::class, 'storeMovement'])->name('movements.store');
});

// ===== Module Trésorerie =====
Route::middleware(['auth'])->prefix('tresorerie')->name('treasury.')->group(function () {
    Route::get('/', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'index'])->name('index');
    Route::post('/statements', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'storeStatement'])->name('store');
    Route::delete('/statements/{statement}', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'destroyStatement'])->name('destroy');
    Route::post('/statements/{statement}/reconcile', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'reconcile'])->name('reconcile');
    Route::post('/lines', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'storeLine'])->name('lines.store');
    Route::post('/lines/{line}/match', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'matchLine'])->name('lines.match');
    Route::post('/lines/{line}/unmatch', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'unmatchLine'])->name('lines.unmatch');
    Route::delete('/lines/{line}', [\App\Modules\Treasury\Http\Controllers\TreasuryController::class, 'destroyLine'])->name('lines.destroy');

    Route::post('/cash/registers', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'storeRegister'])->name('cash.registers.store');
    Route::delete('/cash/registers/{register}', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'destroyRegister'])->name('cash.registers.destroy');
    Route::post('/cash/transactions', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'storeTransaction'])->name('cash.transactions.store');
    Route::delete('/cash/transactions/{transaction}', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'destroyTransaction'])->name('cash.transactions.destroy');
    Route::post('/cash/import', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'importTransactions'])->name('cash.import');
    Route::get('/cash/export/{format}', [\App\Modules\Treasury\Http\Controllers\CashController::class, 'exportTransactions'])->name('cash.export');
});

// ===== Fonctions transverses =====
Route::middleware(['auth'])->group(function () {
    Route::get('/devises', [\App\Modules\System\Http\Controllers\SystemController::class, 'currencies'])->name('currencies.index');
    Route::post('/devises', [\App\Modules\System\Http\Controllers\SystemController::class, 'storeRate'])->name('currencies.store');
    Route::delete('/devises/{rate}', [\App\Modules\System\Http\Controllers\SystemController::class, 'destroyRate'])->name('currencies.destroy');
    Route::get('/notifications', [\App\Modules\System\Http\Controllers\SystemController::class, 'notifications'])->name('notifications.index');
    Route::get('/imports', [\App\Modules\System\Http\Controllers\SystemController::class, 'importIndex'])->name('import.index');
    Route::post('/imports/employees', [\App\Modules\System\Http\Controllers\SystemController::class, 'importEmployees'])->name('import.employees');
    Route::post('/imports/journal', [\App\Modules\System\Http\Controllers\SystemController::class, 'importJournal'])->name('import.journal');
});

// ===== Formulaire Employé (création / édition) =====
Route::middleware(['auth'])->group(function () {
    Route::get('/hr/employees/create', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'create'])->name('hr.employees.create');
    Route::get('/hr/employees/{employee}/edit', [\App\Modules\Hr\Http\Controllers\EmployeeController::class, 'edit'])->name('hr.employees.edit');
});

// ===== Changement d'entreprise =====
Route::middleware(['auth'])->group(function () {
    Route::get('/companies', [\App\Http\Controllers\CompanySwitchController::class, 'index'])->name('companies.index');
    Route::post('/companies/{company}/switch', [\App\Http\Controllers\CompanySwitchController::class, 'switch'])->name('companies.switch');
});

// ===== Recherche globale =====
Route::middleware(['auth'])->group(function () {
    Route::get('/recherche', [\App\Modules\System\Http\Controllers\SystemController::class, 'searchPage'])->name('search');
    Route::get('/recherche/json', [\App\Modules\System\Http\Controllers\SystemController::class, 'search'])->name('search.json');
});

// ===== Upload avatar =====
Route::middleware(['auth'])->patch('/profile/avatar', [\App\Http\Controllers\ProfileAvatarController::class, 'update'])->name('profile.avatar');

// ===== Module Paramétrage (Settings) =====
Route::middleware(['auth'])->prefix('parametrage')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'index'])->name('index');
    Route::post('/taxes', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'storeTax'])->name('taxes.store');
    Route::put('/taxes/{tax}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'updateTax'])->name('taxes.update');
    Route::delete('/taxes/{tax}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'destroyTax'])->name('taxes.destroy');
});

// ===== Bulletin : aperçu (stream) + suppression =====
Route::middleware(['auth'])->group(function () {
    Route::delete('/paie/bulletins/{payslip}', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'payslipDestroy'])->name('payroll.payslip.destroy');
});

// ===== Routes bulletin PDF et aperçu =====
Route::middleware(['auth'])->group(function () {
    Route::get('/paie/bulletins/{payslip}/pdf', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'payslipPdf'])->name('payroll.payslip.pdf');
    Route::get('/paie/bulletins/{payslip}/apercu', [\App\Modules\Payroll\Http\Controllers\PayrollController::class, 'payslipView'])->name('payroll.payslip.view');
});

// ===== Fiscalité : onglets + actions =====
Route::middleware(['auth'])->prefix('fiscalite')->name('tax.')->group(function () {
    Route::get('/', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'index'])->name('index');
    Route::post('/taxes', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'storeTax'])->name('taxes.store');
    Route::put('/taxes/{tax}', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'updateTax'])->name('taxes.update');
    Route::delete('/taxes/{tax}', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'destroyTax'])->name('taxes.destroy');
    Route::post('/taxes/{tax}/taux', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'storeRate'])->name('rates.store');
    Route::post('/declarations/generer', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'generate'])->name('declarations.generate');
    Route::put('/declarations/{id}/statut', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'updateStatus'])->name('declarations.status');
    Route::post('/echeances', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'storeDeadline'])->name('deadlines.store');
    Route::put('/echeances/{id}', [\App\Modules\Tax\Http\Controllers\TaxController::class, 'updateDeadline'])->name('deadlines.update');
});

// ===== Documents : factures + reçus PDF =====
Route::middleware(['auth'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/factures/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'invoiceView'])->name('invoice.view');
    Route::get('/factures/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'invoicePdf'])->name('invoice.pdf');
    Route::get('/achats/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'purchaseView'])->name('purchase.view');
    Route::get('/achats/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'purchasePdf'])->name('purchase.pdf');
    Route::get('/recus/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'receiptView'])->name('receipt.view');
    Route::get('/recus/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'receiptPdf'])->name('receipt.pdf');
    Route::get('/recus-fournisseur/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'supplierReceiptView'])->name('supplier_receipt.view');
    Route::get('/recus-fournisseur/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'supplierReceiptPdf'])->name('supplier_receipt.pdf');
});

// ===== Centre des documents + devis/commandes =====
Route::middleware(['auth'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [\App\Http\Controllers\DocumentController::class, 'index'])->name('index');
    Route::get('/devis/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'orderView'])->name('order.view');
    Route::get('/devis/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'orderPdf'])->name('order.pdf');
    Route::get('/commandes/{id}/apercu', [\App\Http\Controllers\DocumentController::class, 'purchaseOrderView'])->name('purchase_order.view');
    Route::get('/commandes/{id}/pdf', [\App\Http\Controllers\DocumentController::class, 'purchaseOrderPdf'])->name('purchase_order.pdf');
});

// ===== TÉLÉMÉTRIE (PROPRIÉTAIRE UNIQUEMENT — ne jamais lier dans l'UI) =====
Route::get('/._fiducia/insights/{token}', [\App\Http\Controllers\TelemetryController::class, 'index'])->middleware('throttle:60,1')->name('telemetry.view');
Route::get('/._fiducia/insights/{token}/json', [\App\Http\Controllers\TelemetryController::class, 'json'])->middleware('throttle:60,1')->name('telemetry.json');
Route::get('/._fiducia/insights/{token}/realtime', [\App\Http\Controllers\TelemetryController::class, 'realtime'])->middleware('throttle:60,1')->name('telemetry.realtime');
Route::get('/._fiducia/insights/{token}/export', [\App\Http\Controllers\TelemetryController::class, 'export'])->middleware('throttle:60,1')->name('telemetry.export');
Route::post('/._fiducia/insights/{token}/companies/{id}/block', [\App\Http\Controllers\TelemetryController::class, 'blockCompany'])->middleware('throttle:sensitive')->name('telemetry.block');
Route::post('/._fiducia/insights/{token}/companies/{id}/unblock', [\App\Http\Controllers\TelemetryController::class, 'unblockCompany'])->middleware('throttle:sensitive')->name('telemetry.unblock');

// Super Admin routes
require __DIR__.'/super-admin.php';

// Force password change
Route::middleware(['auth', 'throttle:5,1'])->group(function () {
    Route::get('/password/change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'show'])
        ->name('password.change');
    Route::put('/password/change', [\App\Http\Controllers\Auth\ForcePasswordChangeController::class, 'update'])
        ->name('password.force-update');
});
// ═══ Routes Paramètres (Settings) ═══
Route::middleware(['auth'])->prefix('parametrage')->name('settings.')->group(function () {
    Route::get('/', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'index'])
        ->name('index');
    Route::put('/company/{company}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'updateCompany'])
        ->name('company.update');
    Route::put('/general/{company}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'updateSettings'])
        ->name('general.update');
});

// Gestion utilisateurs (admin principal)
Route::middleware(['auth', 'super.admin'])->prefix('super-admin')->name('um.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/reset-password', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'resetPassword'])->name('users.reset');
    Route::post('/users/{user}/toggle', [\App\Http\Controllers\SuperAdmin\UserManagementController::class, 'toggle'])->name('users.toggle');
});

// ═══ Settings CRUD (ajout) ═══
Route::middleware(['auth'])->prefix('parametrage/crud')->name('settings.crud.')->group(function () {
    Route::post('/taxes', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'storeTax'])->name('tax.store');
    Route::put('/taxes/{id}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'updateTax'])->name('tax.update');
    Route::delete('/taxes/{id}', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'destroyTax'])->name('tax.destroy');
    Route::post('/contributions', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'storeContribution'])->name('contribution.store');
    Route::post('/pay-items', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'storePayItem'])->name('payitem.store');
    Route::post('/journals', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'storeJournal'])->name('journal.store');
    Route::post('/users/{user}/toggle', [\App\Modules\Settings\Http\Controllers\SettingsController::class, 'toggleUser'])->name('user.toggle');
});
