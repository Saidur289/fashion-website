<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Theme license verification
    |--------------------------------------------------------------------------
    |
    | SafeBuy checks its license against the theme license server. The guard is
    | OFF until a key is set, so local development and tests are never blocked.
    | Give the customer their own key in the live .env to switch it on.
    |
    */

    // The license server base URL (no trailing slash needed).
    'server' => env('LICENSE_SERVER_URL', 'https://theme-license-server-delta.vercel.app'),

    // The customer's license key, e.g. TL-1A2B-3C4D-5E6F-7A8B. Empty = guard off.
    'key' => env('LICENSE_KEY', ''),

    // This theme's slug (kept on the server for your records).
    'theme' => env('LICENSE_THEME', 'safebuy'),

    // How often (seconds) to recheck with the server. 600 = every 10 minutes.
    'cache_ttl' => (int) env('LICENSE_CACHE_TTL', 600),

    // How long (seconds) a licensed site keeps working if the server is
    // unreachable, before it blocks. 259200 = 3 days.
    'grace' => (int) env('LICENSE_GRACE', 259200),
];
