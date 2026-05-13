<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Catat activity ke database
     */
    public static function log(string $event, ?int $userId = null, ?Request $request = null): ActivityLog
    {
        $request ??= request();
        $parsed    = self::parseUserAgent($request->userAgent() ?? '');

        return ActivityLog::create([
            'user_id'         => $userId,
            'event'           => $event,
            'ip_address'      => $request->ip(),
            'browser'         => $parsed['browser'],
            'browser_version' => $parsed['browser_version'],
            'platform'        => $parsed['platform'],
            'device_type'     => $parsed['device_type'],
            'user_agent'      => $request->userAgent(),
            'url'             => $request->fullUrl(),
        ]);
    }

    /**
     * Parse User-Agent string secara manual (tanpa library eksternal)
     * Deteksi: browser, versi, platform, device type
     */
    public static function parseUserAgent(string $ua): array
    {
        $ua = trim($ua);

        // ── Browser Detection ──────────────────────────────────────────────
        $browser        = 'Unknown';
        $browserVersion = '';

        $browsers = [
            // Urutan PENTING: cek yang lebih spesifik duluan
            'Opera'             => '/(?:Opera|OPR)[\/ ]([\d.]+)/',
            'Edge'              => '/Edg(?:e|A|iOS)?[\/ ]([\d.]+)/',
            'Samsung Browser'   => '/SamsungBrowser\/([\d.]+)/',
            'UC Browser'        => '/UCBrowser\/([\d.]+)/',
            'YaBrowser'         => '/YaBrowser\/([\d.]+)/',
            'Vivaldi'           => '/Vivaldi\/([\d.]+)/',
            'Brave'             => '/Brave\/([\d.]+)/',
            'Firefox'           => '/(?:Firefox|FxiOS)\/([\d.]+)/',
            'Chrome'            => '/(?:Chrome|CriOS)\/([\d.]+)/',
            'Safari'            => '/Version\/([\d.]+).*Safari/',
            'Internet Explorer' => '/(?:MSIE |Trident.*rv:)([\d.]+)/',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $ua, $m)) {
                $browser        = $name;
                $browserVersion = $m[1] ?? '';
                break;
            }
        }

        // ── Platform / OS Detection ────────────────────────────────────────
        $platform = 'Unknown';

        $platforms = [
            'Android'       => '/Android/',
            'iOS'           => '/iPhone|iPad|iPod/',
            'Windows Phone' => '/Windows Phone/',
            'Windows'       => '/Windows NT/',
            'macOS'         => '/Macintosh|Mac OS X/',
            'Linux'         => '/Linux/',
            'ChromeOS'      => '/CrOS/',
        ];

        foreach ($platforms as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                $platform = $name;
                break;
            }
        }

        // ── Device Type Detection ──────────────────────────────────────────
        $deviceType = 'unknown';

        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|phone|mini|palm|windows phone/i', $ua)) {
            $deviceType = 'mobile';
        } elseif (preg_match('/windows|macintosh|linux|x11|cros/i', $ua)) {
            // Cek lagi: Android bisa ada di desktop UA (emulator), handle
            if (! preg_match('/mobile/i', $ua)) {
                $deviceType = 'desktop';
            } else {
                $deviceType = 'mobile';
            }
        }

        return [
            'browser'         => $browser,
            'browser_version' => $browserVersion,
            'platform'        => $platform,
            'device_type'     => $deviceType,
        ];
    }
}
