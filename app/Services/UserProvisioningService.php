<?php
namespace App\Services;

use App\Mail\UserInvitationMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class UserProvisioningService
{
    public function createUser(array $data, User $invitedBy): array
    {
        $companyId = (int) ($data['company_id'] ?? 0);
        $requestedRole = (string) ($data['role'] ?? 'employee');
        $modules = array_filter(array_map('intval', $data['modules'] ?? []));
        $modulePermissions = $data['module_permissions'] ?? [];

        if ($this->isCompanyAdmin($invitedBy) && $this->isSystemAdminRole($requestedRole)) {
            throw new \RuntimeException('Un administrateur d\'entreprise ne peut pas créer un compte administrateur système.');
        }

        if ($this->isCompanyAdmin($invitedBy) && $companyId > 0) {
            $company = Company::find($companyId);
            if (! $company || ! $invitedBy->companies()->where('companies.id', $company->id)->exists()) {
                throw new \RuntimeException('Vous ne pouvez créer des utilisateurs que pour votre propre entreprise.');
            }
        }

        $tempPassword = $this->generatePassword();
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name' => $data['name'],
                'password' => Hash::make($tempPassword),
                'email_verified_at' => now(),
                'must_change_password' => true,
                'is_active' => true,
            ]
        );

        if ($companyId > 0) {
            $user->companies()->syncWithoutDetaching([$companyId => ['role' => $requestedRole, 'is_active' => true]]);
        }

        $user->assignModules($modules);

        if (!empty($modulePermissions) && is_array($modulePermissions)) {
            $syncData = [];
            foreach ($modulePermissions as $permission) {
                $moduleId = (int) ($permission['module_id'] ?? 0);
                if ($moduleId <= 0) {
                    continue;
                }

                $syncData[$moduleId] = [
                    'can_view' => (bool) ($permission['can_view'] ?? true),
                    'can_create' => (bool) ($permission['can_create'] ?? false),
                    'can_edit' => (bool) ($permission['can_edit'] ?? false),
                    'can_delete' => (bool) ($permission['can_delete'] ?? false),
                ];
            }

            if (!empty($syncData)) {
                $user->modules()->sync($syncData);
            }
        }

        if (method_exists($user, 'syncRoles')) {
            $role = Role::firstOrCreate(['name' => $requestedRole, 'guard_name' => 'web']);
            $user->syncRoles([$role]);
        }

        $emailSent = false;
        try {
            Mail::to($user->email)->send(new UserInvitationMail($user, $tempPassword, $invitedBy->name));
            $emailSent = true;
        } catch (\Exception $e) {
            Log::error('Email échoué: '.$e->getMessage());
        }

        return ['user' => $user, 'temp_password' => $tempPassword, 'email_sent' => $emailSent];
    }

    private function isCompanyAdmin(User $user): bool
    {
        return ! $user->isSuperAdmin() && $user->companies()->wherePivot('role', 'admin')->exists();
    }

    private function isSystemAdminRole(string $role): bool
    {
        return in_array($role, ['super-admin', 'admin-company', 'admin'], true);
    }

    private function generatePassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789@#%&';
        $pwd = 'Fa' . random_int(10, 99);
        for ($i = 0; $i < 8; $i++) {
            $pwd .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return str_shuffle($pwd);
    }
}
