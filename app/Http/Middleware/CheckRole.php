<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<string>  $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Non authentifié.');
        }

        if (!$user->hasAnyRole($roles)) {
            abort(403, 'Accès refusé. Rôle insuffisant.');
        }

        return $next($request);
    }
}
