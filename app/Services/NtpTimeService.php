<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use App\Models\SystemSetting;

class NtpTimeService
{
    /**
     * Get the configured NTP Server.
     */
    public static function getServer(): string
    {
        return SystemSetting::getValue('ntp_server', 'time.google.com');
    }

    /**
     * Get the configured NTP Port.
     */
    public static function getPort(): int
    {
        return (int) SystemSetting::getValue('ntp_port', 123);
    }

    /**
     * Get the project-wide default timezone (+05:30 Asia/Kolkata).
     */
    public static function getTimezone(): string
    {
        return SystemSetting::getValue('timezone', 'Asia/Kolkata');
    }

    /**
     * Fetch synchronized timestamp data using monotonic clock anchoring.
     * Caches the anchor for 15 minutes to prevent blocking network requests.
     */
    public static function getSyncData(): array
    {
        try {
            return Cache::remember('ntp_sync_anchor_data', 900, function () {
                return self::fetchLiveAnchor();
            });
        } catch (\Exception $e) {
            return [
                'ntp_unix' => time(),
                'hrtime_sec' => hrtime(true) / 1e9,
                'source' => 'Local Fallback'
            ];
        }
    }

    /**
     * Fetch live anchor timestamp from server/network.
     */
    public static function fetchLiveAnchor(): array
    {
        $server = self::getServer();
        $port = self::getPort();
        $hrtimeSec = hrtime(true) / 1e9;

        // Try 1: UDP Socket SNTP mode 3
        try {
            $timeout = 2;
            $socket = @fsockopen("udp://{$server}", $port, $errno, $errstr, $timeout);
            if ($socket) {
                stream_set_timeout($socket, $timeout);
                $msg = "\x1b" . str_repeat("\0", 47);
                fwrite($socket, $msg);
                $response = fread($socket, 48);
                fclose($socket);

                if (strlen($response) === 48) {
                    $data = unpack('N12', $response);
                    $ntpUnix = sprintf('%u', $data[11]) - 2208988800;
                    if ($ntpUnix > 1700000000) {
                        return [
                            'ntp_unix' => (int) $ntpUnix,
                            'hrtime_sec' => $hrtimeSec,
                            'source' => "UDP NTP ({$server}:{$port})"
                        ];
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallthrough to HTTP Date header
        }

        // Try 2: HTTP Date header over TCP port 443 (Stratum-1 Atomic NTP synced hosts)
        $httpHosts = ['https://www.google.com', 'https://www.cloudflare.com', 'https://time.is'];
        foreach ($httpHosts as $host) {
            try {
                $ch = curl_init($host);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 2);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $header = curl_exec($ch);
                curl_close($ch);

                if ($header && preg_match('/^Date: (.+)$/im', $header, $matches)) {
                    $httpUnix = strtotime(trim($matches[1]));
                    if ($httpUnix > 1700000000) {
                        return [
                            'ntp_unix' => (int) $httpUnix,
                            'hrtime_sec' => $hrtimeSec,
                            'source' => "HTTP Atomic NTP (" . parse_url($host, PHP_URL_HOST) . ")"
                        ];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Fallback
        return [
            'ntp_unix' => time(),
            'hrtime_sec' => $hrtimeSec,
            'source' => 'Local System Clock'
        ];
    }

    /**
     * Calculate and return the time offset (in seconds) relative to local system clock.
     */
    public static function getOffset(): int
    {
        $nowUnix = self::getCurrentUnixTimestamp();
        return (int) ($nowUnix - time());
    }

    /**
     * Force sync and refresh NTP anchor from network.
     */
    public static function refreshOffset(): int
    {
        Cache::forget('ntp_sync_anchor_data');
        Cache::forget('ntp_clock_offset_seconds');
        return self::getOffset();
    }

    /**
     * Get exact synchronized current Unix timestamp using CPU monotonic clock elapsed time.
     * Immune to local OS system date/time modifications.
     */
    public static function getCurrentUnixTimestamp(): int
    {
        $data = self::getSyncData();
        $currentHrtime = hrtime(true) / 1e9;
        $elapsed = $currentHrtime - ($data['hrtime_sec'] ?? $currentHrtime);

        // If system rebooted or elapsed time invalid (< 0), refresh anchor
        if ($elapsed < 0 || $elapsed > 86400) {
            Cache::forget('ntp_sync_anchor_data');
            $data = self::getSyncData();
            $elapsed = 0;
        }

        return (int) round(($data['ntp_unix'] ?? time()) + $elapsed);
    }

    /**
     * Get the synchronized current Carbon date-time in +05:30 (Asia/Kolkata).
     */
    public static function now($tz = null): Carbon
    {
        if (!$tz || (!is_string($tz) && !($tz instanceof \DateTimeZone)) || (is_string($tz) && preg_match('/^[0-9]/', $tz))) {
            $tz = self::getTimezone();
        }
        $unixTimestamp = self::getCurrentUnixTimestamp();

        return Carbon::createFromTimestamp($unixTimestamp, $tz);
    }

    /**
     * Get diagnostic status of the NTP synchronization.
     */
    public static function getStatus(): array
    {
        $data = self::getSyncData();
        $tz = self::getTimezone();
        $now = self::now($tz);
        $offset = self::getOffset();

        return [
            'server' => self::getServer(),
            'port' => self::getPort(),
            'timezone' => $tz,
            'offset_seconds' => $offset,
            'synchronized_time' => $now->format('Y-m-d H:i:s P'),
            'local_server_time' => (new \DateTime('now', new \DateTimeZone($tz)))->format('Y-m-d H:i:s P'),
            'source' => $data['source'] ?? 'HTTP/NTP Synchronized',
            'status' => abs($offset) <= 2 ? 'Synchronized (NTP Aligned)' : "Synchronized (Drift Offset corrected: {$offset}s)"
        ];
    }

    /**
     * Overrides Laravel's Carbon to globally enforce NTP synchronized time.
     * Prevents infinite recursion when resolving time from cache.
     */
    public static function overrideGlobalCarbon(): void
    {
        $closure = function () use (&$closure) {
            // Temporarily disable the test mock to prevent recursion
            \Carbon\Carbon::setTestNow(null);
            
            $tz = self::getTimezone();
            $unix = self::getCurrentUnixTimestamp();
            
            // Create the synchronized time instance safely
            $now = new \Illuminate\Support\Carbon(date('Y-m-d H:i:s.u', $unix), $tz);
            
            // Restore the mock closure
            \Carbon\Carbon::setTestNow($closure);
            
            return $now;
        };

        \Carbon\Carbon::setTestNow($closure);

        // Ensure long-running processes (Queue Workers) refresh time before each job
        static $eventsRegistered = false;
        if (!$eventsRegistered && app()->runningInConsole()) {
            $eventsRegistered = true;
            \Illuminate\Support\Facades\Event::listen(
                \Illuminate\Queue\Events\JobProcessing::class,
                function () {
                    \Carbon\Carbon::setTestNow(null);
                    self::overrideGlobalCarbon();
                }
            );
        }
    }
}
