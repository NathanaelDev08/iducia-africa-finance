<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ForcePasswordChangeController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/ForcePasswordChange');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
        ]);

        $user = $request->user();

        // Empêcher de réutiliser le mot de passe temporaire
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent du mot de passe temporaire.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
            'password_changed_at' => now(),
            'temp_password_token' => null,
            'temp_password_expires_at' => null,
        ]);

        return redirect('/dashboard')->with('success', 'Mot de passe modifié avec succès ! Bienvenue sur FIDUCIA ERP.');
    }
}
