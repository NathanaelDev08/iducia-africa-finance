<?php

require __DIR__.'/vendor/autoload.php';

$classes = [
    'App\Http\Controllers\ErpDashboardController',
    'App\Modules\Reporting\Services\DashboardService',
    'App\Modules\Reporting\Services\AccountingReportService',
    'App\Modules\Reporting\Providers\ReportingServiceProvider',
    'App\Http\Controllers\Saas\CompanyController',
    'App\Models\Company',
];

foreach ($classes as $class) {
    echo $class . ' => ' . (class_exists($class) ? 'OK' : 'KO') . PHP_EOL;
}
