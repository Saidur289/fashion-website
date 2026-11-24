<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks SafeBuy's license against the theme license server on each request.
 *
 * It does nothing until a license key is configured, so local dev and tests
 * run normally. When a key is set and the server has revoked it, the license
 * guard prints a "not licensed" page and stops the request.
 */
class VerifyLicense
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) config('license.key', '');

        if ($key !== '') {
            require_once base_path('app/Support/license-guard.php');

            // Blocks and exits here if the license is revoked or invalid.
            license_guard(
                (string) config('license.server'),
                $key,
                (string) config('license.theme', 'safebuy'),
                [
                    'cache_ttl' => (int) config('license.cache_ttl', 600),
                    'grace' => (int) config('license.grace', 259200),
                ]
            );
        }

        return $next($request);
    }
}
