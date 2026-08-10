<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TrackSession
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user || !Schema::hasTable('telemetry_sessions')) {
            return $next($request);
        }

        $sessionId = session()->getId();
        $now = now();

        // Détection basique sans dépendance externe
        $userAgent = $request->userAgent() ?? '';
        $deviceType = 'desktop';
        if (stripos($userAgent, 'mobile') !== false || stripos($userAgent, 'android') !== false) {
            $deviceType = 'mobile';
        } elseif (stripos($userAgent, 'tablet') !== false || stripos($userAgent, 'ipad') !== false) {
            $deviceType = 'tablet';
        }

        // Extraction basique du navigateur
        $browser = 'Unknown';
        if (stripos($userAgent, 'chrome') !== false) $browser = 'Chrome';
        elseif (stripos($userAgent, 'firefox') !== false) $browser = 'Firefox';
        elseif (stripos($userAgent, 'safari') !== false) $browser = 'Safari';
        elseif (stripos($userAgent, 'edge') !== false) $browser = 'Edge';

        // OS basique
        $os = 'Unknown';
        if (stripos($userAgent, 'windows') !== false) $os = 'Windows';
        elseif (stripos($userAgent, 'mac') !== false) $os = 'macOS';
        elseif (stripos($userAgent, 'linux') !== false) $os = 'Linux';
        elseif (stripos($userAgent, 'android') !== false) $os = 'Android';
        elseif (stripos($userAgent, 'ios') !== false || stripos($userAgent, 'iphone') !== false) $os = 'iOS';

        try {
            DB::table('telemetry_sessions')->updateOrInsert(
                ['session_id' => $sessionId],
                [
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'device_type' => $deviceType,
                    'browser' => $browser,
                    'os' => $os,
                    'started_at' => $now,
                    'ended_at' => $now,
                    'pages_viewed' => DB::raw('pages_viewed + 1'),
                    'updated_at' => $now,
                ]
            );
        } catch (\Throwable $e) {
            // Silencieux : ne jamais bloquer l'app
        }

        // Track event page_view
        try {
            DB::table('telemetry_events')->insert([
                'event_name' => 'page_view',
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'metadata' => json_encode(['path' => $request->path()]),
                'occurred_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            // Silencieux
        }

        return $next($request);
    }
}
