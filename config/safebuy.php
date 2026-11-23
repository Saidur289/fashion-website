<?php

/*
 * Shop settings defaults (from .env). Any value saved on /admin/settings-page
 * overrides these at boot via App\Services\Settings::applyToConfig().
 * Leave a value empty to hide it on the site (e.g. phone and hours until confirmed).
 */
return [
    // ---- shop details (footer, emails, SMS) ----
    'email' => env('SAFEBUY_EMAIL', 'safebuy70@gmail.com'),
    'address' => env('SAFEBUY_ADDRESS', 'Jhenaidah, Bangladesh'),
    'phone' => env('SAFEBUY_PHONE'),
    'hours' => env('SAFEBUY_HOURS'),

    // ---- homepage ----
    'budget_limit' => (int) env('SAFEBUY_BUDGET_LIMIT', 1000),   // "Under Tk X" section

    // ---- delivery (Taka) ----
    'shipping' => [
        'inside_dhaka' => (int) env('SHIPPING_INSIDE_DHAKA', 70),
        'outside_dhaka' => (int) env('SHIPPING_OUTSIDE_DHAKA', 130),
        'pickup_enabled' => (bool) env('SHIPPING_PICKUP_ENABLED', false), // collect from the shop, free
        'pickup_address' => env('SHIPPING_PICKUP_ADDRESS', env('SAFEBUY_ADDRESS', 'Jhenaidah, Bangladesh')),
        // Orders at or above this subtotal ship free. 0 = never free.
        'free_over' => (int) env('SAFEBUY_FREE_SHIPPING', 3000),
        // Districts charged the "inside Dhaka" rate.
        'inside_districts' => array_filter(array_map('trim', explode(',', env('SHIPPING_INSIDE_DISTRICTS', 'Dhaka')))),
    ],

    // ---- payment ----
    'cod_enabled' => (bool) env('PAYMENT_COD_ENABLED', true),
    'bkash' => [
        'enabled' => (bool) env('BKASH_ENABLED', true),
        'number' => env('BKASH_NUMBER'),                  // the number customers send money to
        'type' => env('BKASH_ACCOUNT_TYPE', 'personal'),  // personal | agent | merchant (changes the wording: Send Money / Cash Out / Payment)
    ],

    // ---- reviews ----
    // false = new reviews wait for the shop owner to approve them (admin panel, step 4).
    'reviews_auto_approve' => (bool) env('REVIEWS_AUTO_APPROVE', false),

    // ---- notifications ----
    // Where new-order alerts go. Empty = no alert.
    'notify_email' => env('ORDER_NOTIFY_EMAIL'),
    'notify_phone' => env('ORDER_NOTIFY_PHONE'),

    'sms' => [
        // log = write SMS to storage/logs (nothing is sent). http = send through the gateway below.
        'driver' => env('SMS_DRIVER', 'log'),
        // Most Bangladeshi gateways (BulkSMSBD, SSL Wireless, Alpha SMS...) take a simple HTTP request.
        // {to}, {message}, {api_key} and {sender_id} are replaced in SMS_HTTP_PARAMS.
        'url' => env('SMS_HTTP_URL'),
        'method' => env('SMS_HTTP_METHOD', 'GET'),
        'params' => env('SMS_HTTP_PARAMS', 'api_key={api_key}&senderid={sender_id}&number={to}&message={message}'),
        'api_key' => env('SMS_API_KEY'),
        'sender_id' => env('SMS_SENDER_ID'),
    ],
];
