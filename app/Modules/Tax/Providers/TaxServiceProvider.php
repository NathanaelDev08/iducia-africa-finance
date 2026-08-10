<?php

namespace App\Modules\Tax\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Tax\Services\VatCalculationService;

class TaxServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Tax';

    public function register(): void
    {
        $this->app->singleton(VatCalculationService::class, function ($app) {
            return new VatCalculationService($app->make(\App\Modules\Accounting\Services\EntryService::class));
        });
    }
}
