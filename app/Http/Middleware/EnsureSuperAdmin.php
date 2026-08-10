<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Vérifier rôle super-admin via Spatie OU via email spécifique
        $isSuperAdmin = false;

        if (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) {
            $isSuperAdmin = true;
        }

        // Fallback : liste d'emails super-admins
        if (!$isSuperAdmin && in_array($user->email, ['nathanaelkouassi55@gmail.com'])) {
            $isSuperAdmin = true;
        }

        if (!$isSuperAdmin) {
            abort(403, 'Accès réservé aux super-administrateurs.');
        }

        return $next($request);
    }
}
