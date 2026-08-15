<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnforceCompanyBlock
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !Schema::hasTable('companies') || !Schema::hasColumn('companies', 'is_blocked')) {
            return $next($request);
        }

        // Le super-admin ne doit jamais pouvoir se verrouiller hors du système
        // en bloquant sa propre entreprise depuis le panneau d'administration.
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        try {
            $activeId = session('active_company_id') ?? session('company_id');
            $ids = $activeId ? [$activeId] : [];
            if (empty($ids)) {
                $ids = DB::table('company_user')->where('user_id', $user->id)->pluck('company_id')->toArray();
            }
            if (!empty($ids)) {
                $blocked = DB::table('companies')->whereIn('id', $ids)->where('is_blocked', true)->count();
                // Bloqué si l'entreprise active (ou toutes ses entreprises) est bloquée
                if ($blocked > 0 && ($activeId || $blocked >= count($ids))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/login?blocked=1');
                }
            }
        } catch (\Throwable $e) {
            // silencieux
        }

        return $next($request);
    }
}
