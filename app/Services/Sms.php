<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends SMS through the gateway set in .env (SMS_DRIVER=http) or only writes them to the log (SMS_DRIVER=log).
 * A failed SMS never breaks an order; it is logged instead.
 */
class Sms
{
    public function send(?string $to, string $message): bool
    {
        if (! $to) {
            return false;
        }
        $to = self::normalize($to);
        $c = config('safebuy.sms');

        if ($c['driver'] !== 'http' || ! $c['url']) {
            Log::info("SMS (not sent, SMS_DRIVER={$c['driver']}) to {$to}: {$message}");

            return true;
        }

        try {
            parse_str(strtr($c['params'], [
                '{to}' => urlencode($to),
                '{message}' => urlencode($message),
                '{api_key}' => urlencode((string) $c['api_key']),
                '{sender_id}' => urlencode((string) $c['sender_id']),
            ]), $params);
            $response = strtoupper($c['method']) === 'POST'
                ? Http::timeout(10)->asForm()->post($c['url'], $params)
                : Http::timeout(10)->get($c['url'], $params);

            if (! $response->successful()) {
                Log::warning("SMS to {$to} failed: HTTP {$response->status()} {$response->body()}");

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("SMS to {$to} failed: {$e->getMessage()}");

            return false;
        }
    }

    /** 01712345678 / +8801712345678 -> 8801712345678 */
    public static function normalize(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        return str_starts_with($digits, '880') ? $digits : '88'.ltrim($digits, '+');
    }
}
