<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminPassword extends Command
{
    protected $signature = 'admin:ensure-password {email} {password}';

    protected $description = 'Créer ou réinitialiser un compte super admin avec le mot de passe donné (usage : dépannage / compte de secours)';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'Administrateur';
        $user->password = Hash::make($password);
        $user->is_active = true;
        $user->must_change_password = true;
        $user->email_verified_at = $user->email_verified_at ?? now();
        $user->save();

        $company = Company::first();
        if ($company && ! $user->companies()->where('companies.id', $company->id)->exists()) {
            $user->companies()->attach($company->id, ['role' => 'super-admin', 'is_active' => true]);
        }

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
            $user->syncRoles(['super-admin']);
        }

        $this->info("Mot de passe défini pour {$email}.");

        return 0;
    }
}
