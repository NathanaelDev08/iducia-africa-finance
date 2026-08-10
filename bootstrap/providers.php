<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Settings\Providers\SettingsServiceProvider::class,
    App\Modules\Saas\Providers\SaasServiceProvider::class,
    App\Modules\Accounting\Providers\AccountingServiceProvider::class,
    App\Modules\Hr\Providers\HrServiceProvider::class,
    App\Modules\Payroll\Providers\PayrollServiceProvider::class,
    App\Modules\Tax\Providers\TaxServiceProvider::class,
    App\Modules\Reporting\Providers\ReportingServiceProvider::class,
    App\Modules\Purchasing\Providers\PurchasingServiceProvider::class,
    App\Modules\Sales\Providers\SalesServiceProvider::class,
    App\Modules\Assets\Providers\AssetsServiceProvider::class,
    App\Modules\Treasury\Providers\TreasuryServiceProvider::class,
    App\Modules\System\Providers\SystemServiceProvider::class,
];
