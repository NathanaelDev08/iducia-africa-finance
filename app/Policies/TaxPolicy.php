<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Tax\Models\VatDeclaration;

class TaxPolicy
{
    /**
     * Determine whether the user can view any tax declarations.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'tax-manager',
            'accountant',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can view the tax declaration.
     */
    public function view(User $user, VatDeclaration $declaration): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();
        
        if (!in_array($declaration->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'tax-manager',
            'accountant',
            'auditor',
        ]);
    }

    /**
     * Determine whether the user can create tax declarations.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'tax-manager',
            'accountant',
        ]);
    }

    /**
     * Determine whether the user can validate the tax declaration.
     */
    public function validate(User $user, VatDeclaration $declaration): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($declaration->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'tax-manager',
        ]);
    }
}
