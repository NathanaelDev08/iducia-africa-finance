<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetActiveCompany
{
    public function handle(Request $request, Closure $next)
    {
        // Si pas d'utilisateur connecté, on passe
        if (!$request->user()) {
            return $next($request);
        }

        $user = $request->user();
        $companies = $user->companies()->wherePivot('is_active', true)->get();

        if ($companies->isEmpty()) {
            return $next($request);
        }

        $activeCompanyId = session('active_company_id');
        $activeCompany = $companies->firstWhere('id', $activeCompanyId) ?? $companies->first();

        if ($activeCompany) {
            session(['active_company_id' => $activeCompany->id]);
            $request->attributes->set('company', $activeCompany);
            app()->instance('current_company', $activeCompany);
            view()->share('currentCompany', $activeCompany);
        }

        return $next($request);
    }
}
