<?php

namespace App\Modules\Accounting\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;

class AccountingServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Accounting';

    public function register(): void
    {
        $this->app->singleton(
            \App\Modules\Accounting\Services\EntryService::class,
            \App\Modules\Accounting\Services\EntryService::class
        );
    }
}
