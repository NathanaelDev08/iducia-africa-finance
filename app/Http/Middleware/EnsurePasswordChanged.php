<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) return $next($request);

        // Routes autorisées même si mot de passe non changé
        $allowed = [
            'password.change',
            'password.update',
            'profile.edit',
            'logout',
            'password.confirm',
        ];

        $routeName = $request->route()?->getName();
        $path = $request->path();

        if ($user->must_change_password
            && !in_array($routeName, $allowed)
            && !str_starts_with($path, 'password')
            && !str_starts_with($path, 'logout')
        ) {
            return redirect()->route('password.change')
                ->with('warning', 'Vous devez changer votre mot de passe pour continuer.');
        }

        // Enregistrer first_login_at si premier accès
        if (is_null($user->first_login_at)) {
            $user->update(['first_login_at' => now()]);
        }

        return $next($request);
    }
}
