<?php

use App\Modules\Payroll\Http\Controllers\PayrollController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'set_active_company'])->prefix('payroll')->name('payroll.')->group(function () {
    Route::get('/', [PayrollController::class, 'index'])->name('index');
    Route::get('/{payRun}', [PayrollController::class, 'show'])->name('show');
    Route::post('/{payRun}/post', [PayrollController::class, 'postToAccounting'])->name('post');
});
