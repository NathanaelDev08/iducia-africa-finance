<?php

namespace App\Modules\Reporting\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Reporting\Services\AccountingReportService;
use App\Modules\Reporting\Services\DashboardService;

class ReportingServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Reporting';

    public function register(): void
    {
        $this->app->singleton(AccountingReportService::class, function () {
            return new AccountingReportService();
        });

        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService($app->make(AccountingReportService::class));
        });
    }
}
