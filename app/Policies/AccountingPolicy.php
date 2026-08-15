<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Accounting\Models\AccountingEntry;

class AccountingPolicy
{
    /**
     * Determine whether the user can view any accounting entries.
     * Rôles autorisés : super-admin, admin-company, accountant, auditor, tax-manager, manager
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'accountant',
            'auditor',
            'tax-manager',
            'manager',
        ]);
    }

    /**
     * Determine whether the user can view the accounting entry.
     */
    public function view(User $user, AccountingEntry $entry): bool
    {
        // Vérifier que l'écriture appartient à une entreprise de l'utilisateur
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($entry->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'accountant',
            'auditor',
            'tax-manager',
            'manager',
        ]);
    }

    /**
     * Determine whether the user can create accounting entries.
     */
    public function create(User $user): bool
    {
        $roles = collect($user->getRoleNames())->map(fn ($role) => strtolower((string) $role))->all();

        if (in_array('super-admin', $roles, true)
            || in_array('admin-company', $roles, true)
            || in_array('accountant', $roles, true)) {
            return true;
        }

        $companyRoles = $user->companies()->pluck('company_user.role')->filter()->map(fn ($role) => strtolower((string) $role))->all();

        return in_array('admin', $companyRoles, true)
            || in_array('accountant', $companyRoles, true);
    }

    /**
     * Determine whether the user can update the accounting entry.
     * Seules les écritures en brouillon peuvent être modifiées.
     */
    public function update(User $user, AccountingEntry $entry): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($entry->company_id, $companyIds)) {
            return false;
        }

        if ($entry->status !== 'draft') {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'accountant',
        ]);
    }

    /**
     * Determine whether the user can validate the accounting entry.
     */
    public function validate(User $user, AccountingEntry $entry): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($entry->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'accountant',
        ]);
    }

    /**
     * Determine whether the user can delete the accounting entry.
     * La suppression est interdite sauf pour super-admin.
     */
    public function delete(User $user, AccountingEntry $entry): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($entry->company_id, $companyIds)) {
            return false;
        }

        return $user->hasRole('super-admin') && $entry->status === 'draft';
    }

    /**
     * Determine whether the user can reverse the accounting entry.
     */
    public function reverse(User $user, AccountingEntry $entry): bool
    {
        $companyIds = $user->companies()->pluck('companies.id')->toArray();

        if (!in_array($entry->company_id, $companyIds)) {
            return false;
        }

        return $user->hasAnyRole([
            'super-admin',
            'admin-company',
            'accountant',
        ]);
    }
}
