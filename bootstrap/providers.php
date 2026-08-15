<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Modules\Settings\Providers\SettingsServiceProvider::class,
    App\Modules\Saas\Providers\SaasServiceProvider::class,
    App\Modules\Accounting\Providers\AccountingServiceProvider::class,
    App\Modules\Hr\Providers\HrServiceProvider::class,
    App\Modules\Payroll\Providers\PayrollServiceProvider::class,
    App\Modules\Tax\Providers\TaxServiceProvider::class,
    App\Modules\Reporting\Providers\ReportingServiceProvider::class,
    App\Modules\Treasury\Providers\TreasuryServiceProvider::class,
];
