<?php

namespace App\Services;

use App\Models\AccessPoint;

class ApWatchdogService
{
    /**
     * Check single Access Point status and latency.
     */
    public static function pingAp(AccessPoint $ap): array
    {
        $ip = trim($ap->ip_address);
        $start = microtime(true);
        $isAlive = false;
        $latency = null;

        // 1. Fast socket check on common AP management ports
        $ports = [80, 443, 8080, 22, 8728];
        foreach ($ports as $port) {
            $socket = @fsockopen($ip, $port, $errno, $errstr, 0.8);
            if ($socket) {
                $isAlive = true;
                fclose($socket);
                $latency = round((microtime(true) - $start) * 1000);
                break;
            }
        }

        // 2. Fallback to ICMP ping if socket didn't connect
        if (!$isAlive) {
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $cmd = $isWindows
                ? "ping -n 1 -w 1000 " . escapeshellarg($ip)
                : "ping -c 1 -W 1 " . escapeshellarg($ip);

            exec($cmd, $output, $code);
            if ($code === 0) {
                $isAlive = true;
                $latency = round((microtime(true) - $start) * 1000);
                // Try to extract exact ping time
                foreach ($output as $line) {
                    if (preg_match('/time[=<]([0-9]+)ms/i', $line, $m)) {
                        $latency = (int) $m[1];
                        break;
                    }
                }
            }
        }

        // Update model status
        if ($isAlive) {
            $status = ($latency !== null && $latency > 200) ? 'degraded' : 'online';
            $ap->update([
                'status'              => $status,
                'last_latency_ms'     => $latency ?: 1,
                'last_seen_at'        => now(),
                'downtime_started_at' => null,
            ]);
        } else {
            $ap->update([
                'status'              => 'offline',
                'last_latency_ms'     => null,
                'downtime_started_at' => $ap->downtime_started_at ?: now(),
            ]);
        }

        return [
            'id'       => $ap->id,
            'name'     => $ap->name,
            'ip'       => $ip,
            'status'   => $ap->status,
            'latency'  => $ap->last_latency_ms,
            'last_seen'=> $ap->last_seen_at?->diffForHumans() ?? 'Belum pernah',
        ];
    }

    /**
     * Ping all APs for a given location.
     */
    public static function pingAll(string $locationId): array
    {
        $aps = AccessPoint::where('location_id', $locationId)->get();
        $results = [];
        foreach ($aps as $ap) {
            $results[] = static::pingAp($ap);
        }
        return $results;
    }
}
