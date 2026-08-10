<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
        {--name= : Nom complet de l\'administrateur}
        {--email= : Email de connexion}
        {--password= : Mot de passe (min 8 caractères)}
        {--company= : ID ou nom de l\'entreprise à attacher}';

    protected $description = 'Créer un compte administrateur avec droits complets';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════');
        $this->info('   CRÉATION COMPTE ADMINISTRATEUR');
        $this->info('═══════════════════════════════════════');

        // Nom
        $name = $this->option('name') ?? $this->ask('Nom complet', 'Administrateur');

        // Email (avec validation)
        $email = $this->option('email');
        while (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->ask('Email de connexion');
        }
        if (User::where('email', $email)->exists()) {
            $this->error("✗ Un utilisateur avec l'email '$email' existe déjà");
            if (!$this->confirm('Voulez-vous le réutiliser ?', true)) return 1;
        }

        // Mot de passe (avec validation)
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Mot de passe (min 8 car.)');
            if (strlen($password) < 8) {
                $this->error('✗ Mot de passe trop court (minimum 8 caractères)');
                return 1;
            }
            $confirm = $this->secret('Confirmer le mot de passe');
            if ($password !== $confirm) {
                $this->error('✗ Les mots de passe ne correspondent pas');
                return 1;
            }
        }

        // Entreprise
        $company = null;
        $companies = Company::all();
        if ($companies->isEmpty()) {
            $this->warn('⚠ Aucune entreprise en base. Créez-en une d\'abord.');
        } elseif ($companies->count() === 1) {
            $company = $companies->first();
            $this->info("✓ Entreprise : {$company->name}");
        } else {
            $this->info('Entreprises disponibles :');
            $companies->each(fn($c) => $this->line("  [{$c->id}] {$c->name}"));
            $companyId = $this->option('company') ?? $this->ask('ID de l\'entreprise', $companies->first()->id);
            $company = Company::find($companyId) ?? $companies->first();
        }

        // Créer l'utilisateur
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // Attacher à l'entreprise avec rôle admin
        if ($company) {
            $user->companies()->syncWithoutDetaching([
                $company->id => ['role' => 'admin']
            ]);
        }

        // Donner tous les rôles admin (si spatie/permission utilisé)
        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
            $user->assignRole($adminRole);
        }

        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  ✅ COMPTE ADMINISTRATEUR CRÉÉ');
        $this->info('═══════════════════════════════════════');
        $this->line("  Email    : <fg=cyan>$email</>");
        $this->line("  Nom      : $name");
        if ($company) $this->line("  Société  : {$company->name}");
        $this->info('═══════════════════════════════════════');

        return 0;
    }
}
