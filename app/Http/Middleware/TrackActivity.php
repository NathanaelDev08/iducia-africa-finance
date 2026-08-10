<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrackActivity
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && Schema::hasColumn('users', 'last_seen_at')) {
            $last = $user->last_seen_at;
            $stale = !$last || \Carbon\Carbon::parse($last)->lt(now()->subHours(6));
            if ($stale) {
                try { DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]); }
                catch (\Throwable $e) {}
            }
        }
        return $next($request);
    }
}
