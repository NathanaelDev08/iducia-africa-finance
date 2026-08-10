<?php

use App\Modules\Reporting\Http\Controllers\ReportingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('rapports')->name('reporting.')->group(function () {
    Route::get('/', [ReportingController::class, 'index'])->name('index');
    Route::get('/balance', [ReportingController::class, 'trialBalance'])->name('trial-balance');
    Route::get('/resultat', [ReportingController::class, 'profitAndLoss'])->name('profit-and-loss');
    Route::get('/bilan', [ReportingController::class, 'balanceSheet'])->name('balance-sheet');
});
