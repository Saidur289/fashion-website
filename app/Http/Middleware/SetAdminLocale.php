<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('admin_locale', 'en');
        if (in_array($locale, ['en', 'bn'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
