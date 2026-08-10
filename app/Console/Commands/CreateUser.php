<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateUser extends Command
{
    protected $signature = 'user:create
        {--name= : Nom complet}
        {--email= : Email}
        {--password= : Mot de passe}
        {--role=admin : Rôle (admin, employee, accountant, hr)}
        {--company= : ID de l\'entreprise}';

    protected $description = 'Créer un utilisateur avec rôle et entreprise';

    public function handle(): int
    {
        $this->info('═══════════════════════════════════════');
        $this->info('   CRÉATION COMPTE UTILISATEUR');
        $this->info('═══════════════════════════════════════');

        // Nom
        $name = $this->option('name') ?? $this->ask('Nom complet');
        if (empty($name)) {
            $this->error('✗ Nom requis');
            return 1;
        }

        // Email
        $email = $this->option('email');
        while (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = $this->ask('Email de connexion');
        }

        // Vérifier si l'email existe déjà
        $existing = User::where('email', $email)->first();
        if ($existing) {
            $this->warn("⚠ L'email '$email' existe déjà (ID: {$existing->id})");
            if (!$this->confirm('Voulez-vous le réutiliser ?', true)) {
                return 1;
            }
        }

        // Mot de passe
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Mot de passe (min 8 caractères)');
            if (strlen($password) < 8) {
                $this->error('✗ Mot de passe trop court (min 8 caractères)');
                return 1;
            }
            $confirm = $this->secret('Confirmer le mot de passe');
            if ($password !== $confirm) {
                $this->error('✗ Les mots de passe ne correspondent pas');
                return 1;
            }
        }

        // Rôle
        $role = $this->option('role') ?? 'employee';
        $validRoles = ['admin', 'employee', 'accountant', 'hr', 'manager', 'super-admin'];
        if (!in_array($role, $validRoles)) {
            $this->warn("⚠ Rôle '$role' invalide. Utilisation de 'employee'");
            $role = 'employee';
        }

        // Entreprise
        $company = null;
        $companies = Company::all();

        if ($companies->isEmpty()) {
            $this->warn('⚠ Aucune entreprise en base. Création automatique...');
            $company = Company::create([
                'name' => 'Entreprise ' . now()->format('YmdHis'),
                'slug' => 'entreprise-' . now()->format('ymdhis'),
                'currency' => 'XOF',
                'timezone' => 'Africa/Abidjan',
            ]);
            $this->info("✓ Entreprise créée : {$company->name}");
        } elseif ($companies->count() === 1) {
            $company = $companies->first();
            $this->info("✓ Entreprise : {$company->name}");
        } else {
            $this->info('Entreprises disponibles :');
            $companies->each(fn($c) => $this->line("  [{$c->id}] {$c->name}"));
            $companyId = $this->option('company') ?? $this->ask('ID de l\'entreprise', $companies->first()->id);
            $company = Company::find($companyId) ?? $companies->first();
        }

        // Créer ou mettre à jour l'utilisateur
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]
        );

        // Attacher à l'entreprise
        if ($company) {
            $user->companies()->syncWithoutDetaching([
                $company->id => ['role' => $role, 'is_active' => true]
            ]);
        }

        // Afficher le résultat
        $this->newLine();
        $this->info('═══════════════════════════════════════');
        $this->info('  ✅ COMPTE CRÉÉ AVEC SUCCÈS');
        $this->info('═══════════════════════════════════════');
        $this->line("  Email    : <fg=cyan>$email</>");
        $this->line("  Nom      : $name");
        $this->line("  Rôle     : <fg=yellow>$role</>");
        if ($company) $this->line("  Société  : {$company->name}");
        $this->info('═══════════════════════════════════════');

        return 0;
    }
}
