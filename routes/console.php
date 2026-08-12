<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('admin:reset-password {email?} {password?}', function (?string $email = null, ?string $password = null) {
    $email = $email ?: $this->ask('Email administrateur');

    $user = App\Models\User::where('email', $email)->first();
    if (! $user) {
        $this->error("Aucun utilisateur trouvé pour l'email \"{$email}\"");
        return 1;
    }

    $password = $password ?: $this->secret('Nouveau mot de passe (min 8 caractères)');
    if (! $password || strlen($password) < 8) {
        $this->error('Le mot de passe doit contenir au moins 8 caractères.');
        return 1;
    }

    $confirm = $this->secret('Confirmation du mot de passe');
    if ($password !== $confirm) {
        $this->error('Les mots de passe ne correspondent pas.');
        return 1;
    }

    $user->forceFill(['password' => Hash::make($password)])->save();

    $this->info("Mot de passe réinitialisé avec succès pour {$email}.");
    return 0;
})->purpose('Réinitialiser le mot de passe d’un administrateur');

Schedule::command('telemetry:snapshot')->dailyAt('02:00');
