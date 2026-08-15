<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Accounting\Http\Controllers\AccountingEntryController;

Route::middleware(['web', 'auth', 'company.required'])->prefix('accounting')->name('accounting.')->group(function () {
    Route::get('entries', [AccountingEntryController::class, 'index'])->name('entries.index');
    Route::post('entries', [AccountingEntryController::class, 'store'])->name('entries.store');
    Route::post('entries/{entry}/validate', [AccountingEntryController::class, 'validate_entry'])->name('entries.validate');
    Route::post('entries/{entry}/reverse', [AccountingEntryController::class, 'reverse'])->name('entries.reverse');
});
