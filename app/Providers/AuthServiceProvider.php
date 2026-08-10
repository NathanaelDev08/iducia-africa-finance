<?php

namespace App\Providers;

use App\Modules\Accounting\Models\AccountingEntry;
use App\Modules\Hr\Models\Employee;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;
use App\Modules\Tax\Models\VatDeclaration;
use App\Policies\AccountingPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\PayrollPolicy;
use App\Policies\TaxPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        AccountingEntry::class => AccountingPolicy::class,
        Employee::class => EmployeePolicy::class,
        PayRun::class => PayrollPolicy::class,
        Payslip::class => PayrollPolicy::class,
        VatDeclaration::class => TaxPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
