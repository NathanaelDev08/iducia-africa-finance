<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->bound('currentCompany')) {
            abort(403, 'Aucune entreprise active sélectionnée.');
        }

        return $next($request);
    }
}
