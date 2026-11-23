<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Loads shop settings stored in the database and applies them onto Laravel's runtime config
 * so every existing call to config('safebuy.*') immediately sees changes made in /admin.
 */
class Settings
{
    private const CACHE_KEY = 'safebuy.settings.all';

    public const DEFAULT_COLORS = [
        'blue' => '#0A449B',
        'blue_dark' => '#073378',
        'blue_light' => '#EAF1FB',
        'orange' => '#F97316',
        'orange_dark' => '#EA580C',
        'orange_light' => '#FFF1E6',
    ];

    public const DEFAULT_SMS_TEMPLATES = [
        'confirmed' => 'SafeBuy: Your order {number} has been confirmed. Total {total}. Thank you!',
        'shipped' => 'SafeBuy: Order {number} has been shipped.{tracking} Track: {track_url}',
        'delivered' => 'SafeBuy: Order {number} has been delivered. Thank you for shopping with SafeBuy!',
        'cancelled' => 'SafeBuy: Order {number} has been cancelled. Contact us if you have any questions.',
        'returned' => 'SafeBuy: Return recorded for order {number}.',
    ];

    public static function all(): array
    {
        try {
            if (! Schema::hasTable('settings')) {
                return [];
            }

            return Cache::remember(self::CACHE_KEY, 3600, fn () => Setting::query()->pluck('value', 'key')->all());
        } catch (\Throwable) {
            return [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return array_key_exists($key, $all) && $all[$key] !== null && $all[$key] !== ''
            ? $all[$key]
            : $default;
    }

    public static function setMany(array $pairs, string $group = 'general'): void
    {
        foreach ($pairs as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => is_bool($value) ? ($value ? '1' : '0') : ($value === null ? null : (string) $value),
                    'group' => $group,
                ],
            );
        }

        self::clearCache();
        self::applyToConfig();
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function colors(): array
    {
        $all = self::all();
        $out = [];
        foreach (self::DEFAULT_COLORS as $k => $default) {
            $val = $all['color_'.$k] ?? null;
            $out[$k] = (is_string($val) && preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) ? $val : $default;
        }

        return $out;
    }

    public static function smsTemplate(string $status): string
    {
        $key = 'sms_tpl_'.$status;

        return (string) self::get($key, self::DEFAULT_SMS_TEMPLATES[$status] ?? 'SafeBuy: Order {number} status is now {status}.');
    }

    /** Push saved database settings into config('safebuy.*'), config('app.name') and config('mail.*'). */
    public static function applyToConfig(): void
    {
        $all = self::all();
        if (! $all) {
            return;
        }

        $str = fn (string $k, ?string $fallback = null) => array_key_exists($k, $all) ? $all[$k] : $fallback;
        $int = fn (string $k, int $fallback) => isset($all[$k]) && $all[$k] !== '' ? (int) $all[$k] : $fallback;
        $bool = fn (string $k, bool $fallback) => array_key_exists($k, $all) && $all[$k] !== null && $all[$k] !== ''
            ? in_array($all[$k], ['1', 'true', true, 1], true)
            : $fallback;

        if (! empty($all['shop_name'])) {
            config(['app.name' => $all['shop_name']]);
        }

        config([
            'safebuy.shop_name' => $str('shop_name', config('app.name', 'SafeBuy')),
            'safebuy.topbar_text' => $str('topbar_text', 'Welcome to SafeBuy — 100% authentic K-Beauty & smart gadgets, delivered to your door'),
            'safebuy.logo' => $str('logo', 'images/brand/safebuy-logo.webp'),
            'safebuy.favicon' => $str('favicon', 'images/brand/safebuy-logo.webp'),
            'safebuy.email' => $str('email', config('safebuy.email')),
            'safebuy.address' => $str('address', config('safebuy.address')),
            'safebuy.phone' => $str('phone', config('safebuy.phone')),
            'safebuy.hours' => $str('hours', config('safebuy.hours')),
            'safebuy.budget_limit' => $int('budget_limit', (int) config('safebuy.budget_limit', 1000)),
            'safebuy.social' => [
                'facebook' => $str('social_facebook', ''),
                'instagram' => $str('social_instagram', ''),
                'youtube' => $str('social_youtube', ''),
                'whatsapp' => $str('social_whatsapp', ''),
            ],
            'safebuy.shipping.inside_dhaka' => $int('shipping_inside_dhaka', (int) config('safebuy.shipping.inside_dhaka', 70)),
            'safebuy.shipping.outside_dhaka' => $int('shipping_outside_dhaka', (int) config('safebuy.shipping.outside_dhaka', 130)),
            'safebuy.shipping.free_over' => $int('shipping_free_over', (int) config('safebuy.shipping.free_over', 3000)),
            'safebuy.shipping.pickup_enabled' => $bool('shipping_pickup_enabled', (bool) config('safebuy.shipping.pickup_enabled', false)),
            'safebuy.shipping.pickup_address' => $str('shipping_pickup_address', config('safebuy.shipping.pickup_address')),
            'safebuy.cod_enabled' => $bool('cod_enabled', (bool) config('safebuy.cod_enabled', true)),
            'safebuy.bkash.enabled' => $bool('bkash_enabled', (bool) config('safebuy.bkash.enabled', true)),
            'safebuy.bkash.number' => $str('bkash_number', config('safebuy.bkash.number')),
            'safebuy.bkash.type' => $str('bkash_type', config('safebuy.bkash.type', 'personal')),
            'safebuy.reviews_auto_approve' => $bool('reviews_auto_approve', (bool) config('safebuy.reviews_auto_approve', false)),
            'safebuy.notify_email' => $str('notify_email', config('safebuy.notify_email')),
            'safebuy.notify_phone' => $str('notify_phone', config('safebuy.notify_phone')),
            'safebuy.sms.driver' => $str('sms_driver', config('safebuy.sms.driver', 'log')),
            'safebuy.sms.url' => $str('sms_url', config('safebuy.sms.url')),
            'safebuy.sms.method' => $str('sms_method', config('safebuy.sms.method', 'GET')),
            'safebuy.sms.params' => $str('sms_params', config('safebuy.sms.params')),
            'safebuy.sms.api_key' => $str('sms_api_key', config('safebuy.sms.api_key')),
            'safebuy.sms.sender_id' => $str('sms_sender_id', config('safebuy.sms.sender_id')),
            'safebuy.colors' => self::colors(),
        ]);

        // Optional SMTP overrides when configured in Admin Settings
        if (! empty($all['mail_mailer'])) {
            config(['mail.default' => $all['mail_mailer']]);
        }
        if (! empty($all['mail_host'])) {
            config([
                'mail.mailers.smtp.host' => $all['mail_host'],
                'mail.mailers.smtp.port' => (int) ($all['mail_port'] ?: 587),
                'mail.mailers.smtp.username' => $all['mail_username'] ?? null,
                'mail.mailers.smtp.password' => $all['mail_password'] ?? null,
            ]);
        }
        if (! empty($all['mail_from_address'])) {
            config([
                'mail.from.address' => $all['mail_from_address'],
                'mail.from.name' => $all['mail_from_name'] ?: config('app.name', 'SafeBuy'),
            ]);
        }
    }
}
