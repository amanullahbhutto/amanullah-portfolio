<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and attach OWASP security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking via iframes (allow only from same origin)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable browser XSS filtering
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Protect referrer information across origins
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Restrict unnecessary browser features & APIs
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Prevent cross-domain policy file abuse (Flash/PDF)
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Hide PHP/server technology fingerprinting
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        // Enforce HTTPS HSTS when connecting via HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}

