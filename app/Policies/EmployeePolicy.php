<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Hr\Models\Employee;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any employees.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'hr-manager',
            'payroll-manager',
            'manager',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can view the employee.
     */
    public function view(User $user, Employee $employee): bool
    {
        // Vérifier l'appartenance à l'entreprise
        $companyIds = $user->companies()->pluck('companies.id')->toArray();
        
        if (!in_array($employee->company_id, $companyIds)) {
            return false;
        }

        // Un employé ne peut voir que son propre dossier
        if ($user->hasRole('employee')) {
            return $employee->user_id === $user->id;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'hr-manager',
            'payroll-manager',
            'manager',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can create employees.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'hr-manager',
        ]);
    }

    /**
     * Determine whether the user can update the employee.
     */
    public function update(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'hr-manager',
        ]);
    }

    /**
     * Determine whether the user can delete the employee.
     */
    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can view sensitive data (salary, bank account).
     */
    public function viewSensitiveData(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'hr-manager',
            'payroll-manager',
        ]);
    }
}
