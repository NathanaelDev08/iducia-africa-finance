<?php
namespace App\Http\Middleware;
use App\Models\SystemModule;
use Closure;
use Illuminate\Http\Request;
class CheckModuleAccess
{
    public function handle(Request $request, Closure $next, string $code)
    {
        $user = $request->user();
        if (!$user) abort(401);
        if ($user->isSuperAdmin()) return $next($request);
        $mod = SystemModule::where('code', $code)->first();
        if ($mod && $mod->is_base_module) return $next($request);
        if (!$user->hasModule($code)) abort(403, 'Acces a ce module non autorise.');
        return $next($request);
    }
}
