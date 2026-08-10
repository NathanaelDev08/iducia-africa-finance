<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Payroll\Models\PayRun;
use App\Modules\Payroll\Models\Payslip;

class PayrollPolicy
{
    /**
     * Determine whether the user can view any pay runs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'payroll-manager',
            'accountant',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can view the pay run.
     */
    public function view(User $user, PayRun $payRun): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();
        
        if (!in_array($payRun->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'payroll-manager',
            'accountant',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can create pay runs.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'payroll-manager',
        ]);
    }

    /**
     * Determine whether the user can validate the pay run.
     */
    public function validate(User $user, PayRun $payRun): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'payroll-manager',
        ]);
    }

    /**
     * Determine whether the user can lock the pay run.
     */
    public function lock(User $user, PayRun $payRun): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
        ]);
    }

    /**
     * Determine whether the user can view payslips.
     */
    public function viewPayslip(User $user, Payslip $payslip): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();
        
        if (!in_array($payslip->company_id, $companyIds)) {
            return false;
        }

        // Un employé ne peut voir que ses propres bulletins
        if ($user->hasRole('employee')) {
            $employee = $payslip->employee;
            return $employee && $employee->user_id === $user->id;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'payroll-manager',
            'accountant',
            'auditor',
        ]);
    }
}
