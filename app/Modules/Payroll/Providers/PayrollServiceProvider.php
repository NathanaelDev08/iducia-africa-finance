<?php
namespace App\Modules\Payroll\Providers;
use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Payroll\Services\PayrollEngine;

class PayrollServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Payroll';
    public function register(): void
    {
        $this->app->singleton(PayrollEngine::class, function () {
            return new PayrollEngine();
        });
    }
}
