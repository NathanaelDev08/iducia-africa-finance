<?php

namespace App\Modules\Hr\Providers;

use App\Modules\Core\Providers\ModuleServiceProvider;
use App\Modules\Hr\Services\EmployeeService;

class HrServiceProvider extends ModuleServiceProvider
{
    protected string $moduleName = 'Hr';

    public function register(): void
    {
        $this->app->singleton(EmployeeService::class, function () {
            return new EmployeeService();
        });
    }
}
