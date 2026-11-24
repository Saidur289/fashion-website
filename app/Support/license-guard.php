<?php
/**
 * Theme License Guard
 * -------------------
 * Drop this file into any theme you sell. Then, near the very TOP of the
 * theme's startup file (before anything is printed), call:
 *
 *     require_once __DIR__ . '/license-guard.php';
 *     license_guard('https://your-server.com', 'TL-XXXX-XXXX-XXXX-XXXX', 'your-theme-slug');
 *
 * What it does:
 *   - Asks your license server whether this key is still valid.
 *   - Remembers the answer for a short time (so it is fast and does not call
 *     the server on every page load).
 *   - If you REVOKE the key on the server, the site stops working on the next
 *     recheck and shows a "not licensed" page instead.
 *   - If your server is temporarily unreachable, the site keeps working for a
 *     grace period, then blocks (so a customer cannot just block the server).
 */

if (!function_exists('license_guard')) {

    /**
     * @param string $server  Your license server URL, e.g. https://license.yoursite.com
     * @param string $key     The customer's license key
     * @param string $theme   This theme's slug (optional, for your records)
     * @param array  $opts     cache_ttl (seconds), grace (seconds), timeout (seconds)
     */
    function license_guard($server, $key, $theme = '', $opts = array())
    {
        $cacheTtl = isset($opts['cache_ttl']) ? (int) $opts['cache_ttl'] : 10;      // recheck every 10 seconds
        $grace    = isset($opts['grace'])     ? (int) $opts['grace']     : 259200;  // 3 days offline grace
        $timeout  = isset($opts['timeout'])   ? (int) $opts['timeout']   : 8;

        $cacheFile = sys_get_temp_dir() . '/licguard_' . md5($server . '|' . $key) . '.json';
        $domain = isset($_SERVER['HTTP_HOST'])
            ? preg_replace('/^www\./', '', strtolower($_SERVER['HTTP_HOST']))
            : '';
        $now = time();

        $cache = @json_decode(@file_get_contents($cacheFile), true);
        $haveCache = is_array($cache) && isset($cache['at']);

        // 1) Fresh cached answer -> trust it.
        if ($haveCache && ($now - $cache['at'] < $cacheTtl)) {
            if (!empty($cache['valid'])) {
                return true;
            }
            license_guard_block(isset($cache['message']) ? $cache['message'] : 'License is not valid.');
        }

        // 2) Ask the server.
        $resp = license_guard_request($server, $key, $domain, $theme, $timeout);

        // 3) Server unreachable -> allow within the grace window, otherwise block.
        if ($resp === null) {
            if ($haveCache && !empty($cache['valid']) && ($now - $cache['at'] < $grace)) {
                return true;
            }
            license_guard_block('The license server could not be reached.');
        }

        // 4) Save the answer and act on it.
        $valid = !empty($resp['valid']);
        @file_put_contents($cacheFile, json_encode(array(
            'at'      => $now,
            'valid'   => $valid,
            'message' => isset($resp['message']) ? $resp['message'] : '',
        )));

        if ($valid) {
            return true;
        }
        license_guard_block(isset($resp['message']) ? $resp['message'] : 'License is not valid.');
    }

    function license_guard_request($server, $key, $domain, $theme, $timeout)
    {
        $url = rtrim($server, '/') . '/api/verify';
        $payload = json_encode(array('key' => $key, 'domain' => $domain, 'theme' => $theme));

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, array(
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => $timeout,
                CURLOPT_CONNECTTIMEOUT => $timeout,
            ));
            $out = curl_exec($ch);
            curl_close($ch);
        } else {
            $ctx = stream_context_create(array('http' => array(
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => $payload,
                'timeout' => $timeout,
            )));
            $out = @file_get_contents($url, false, $ctx);
        }

        if ($out === false || $out === null || $out === '') {
            return null;
        }
        $data = json_decode($out, true);
        return is_array($data) ? $data : null;
    }

    function license_guard_block($message)
    {
        if (!headers_sent()) {
            header('HTTP/1.1 403 Forbidden');
            header('Content-Type: text/html; charset=utf-8');
        }
        echo '<!doctype html><meta charset="utf-8"><title>License required</title>';
        echo '<div style="font-family:system-ui,-apple-system,Segoe UI,sans-serif;max-width:520px;'
            . 'margin:12vh auto;padding:30px;border-radius:16px;background:#0f172a;color:#e2e8f0;text-align:center">';
        echo '<h1 style="font-size:20px;margin:0 0 10px">This site is not licensed</h1>';
        echo '<p style="color:#94a3b8;margin:0 0 6px">' . htmlspecialchars($message) . '</p>';
        echo '<p style="color:#64748b;font-size:13px;margin:0">Please contact the theme provider.</p></div>';
        exit;
    }
}
