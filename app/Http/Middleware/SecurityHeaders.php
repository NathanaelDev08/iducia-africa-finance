<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // En-têtes de protection (tous environnements)
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=()');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Uniquement en production (ne casse pas le dev Vite)
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set('Content-Security-Policy',
                "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; img-src 'self' data:; " .
                "font-src 'self' data: https://fonts.bunny.net; " .
                "connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'self'"
            );
        }

        return $response;
    }
}
