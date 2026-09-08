<?php

namespace App\Services;

use App\Models\Location;
use RouterOS\Client;
use RouterOS\Query;
use Throwable;

class MikrotikService
{
    protected Location $location;
    protected ?Client $client = null;

    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    // ============================================================
    // Connection Management
    // ============================================================

    /**
     * Build and cache a RouterOS API client for the given location.
     */
    protected function connect(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        $this->client = new Client([
            'host'     => $this->location->router_ip,
            'user'     => $this->location->router_user,
            'pass'     => $this->location->router_password,
            'port'     => (int) $this->location->router_port,
            'timeout'  => config('mikrotik.timeout', 5),
            'attempts' => config('mikrotik.attempts', 3),
            'delay'    => config('mikrotik.delay', 1),
        ]);

        return $this->client;
    }

    /**
     * Test TCP socket connectivity and API authentication.
     * Returns ['connected' => bool, 'latency_ms' => int|null, 'error' => string|null]
     */
    public function testConnection(): array
    {
        if (empty($this->location->router_ip)) {
            return ['connected' => false, 'latency_ms' => null, 'error' => 'Router IP not configured'];
        }

        $start = microtime(true);

        try {
            // First test raw TCP socket
            $socket = @fsockopen(
                $this->location->router_ip,
                (int) $this->location->router_port,
                $errno,
                $errstr,
                config('mikrotik.timeout', 5)
            );

            if (!$socket) {
                return [
                    'connected'  => false,
                    'latency_ms' => null,
                    'error'      => "TCP Error [$errno]: $errstr",
                ];
            }

            fclose($socket);
            $tcpLatency = round((microtime(true) - $start) * 1000);

            // Then test API authentication and retrieve system telemetry
            $client = $this->connect();
            $latency = round((microtime(true) - $start) * 1000);

            $resource = [];
            $identity = [];
            try {
                $resource = $client->query(new Query('/system/resource/print'))->read();
                $identity = $client->query(new Query('/system/identity/print'))->read();
            } catch (Throwable $e) {
                // Non-fatal if specific query fails
            }

            $res = $resource[0] ?? [];
            $ident = $identity[0] ?? [];

            return [
                'connected'    => true,
                'latency_ms'   => $latency,
                'tcp_ms'       => $tcpLatency,
                'version'      => $res['version'] ?? 'RouterOS',
                'board_name'   => $res['board-name'] ?? 'MikroTik Router',
                'cpu_load'     => isset($res['cpu-load']) ? $res['cpu-load'] . '%' : 'N/A',
                'free_memory'  => isset($res['free-memory']) ? round($res['free-memory'] / 1048576, 1) . ' MB' : 'N/A',
                'total_memory' => isset($res['total-memory']) ? round($res['total-memory'] / 1048576, 1) . ' MB' : 'N/A',
                'uptime'       => $res['uptime'] ?? 'N/A',
                'identity'     => $ident['name'] ?? $this->location->name,
                'error'        => null,
            ];
        } catch (Throwable $e) {
            return [
                'connected'  => false,
                'latency_ms' => null,
                'error'      => $e->getMessage(),
            ];
        }
    }

    // ============================================================
    // Hotspot User Management
    // ============================================================

    /**
     * Add or update a hotspot user on the MikroTik router.
     *
     * @param string $username  Hotspot username
     * @param string $password  Hotspot password
     * @param string $profile   Hotspot profile name
     * @param string $mac       Client MAC address (optional, for MAC-binding)
     * @param string $comment   Optional comment (e.g., "survey|campaign-uuid")
     * @return array ['success' => bool, 'error' => string|null]
     */
    public function authorizeUser(
        string $username,
        string $password,
        string $profile = '',
        string $mac = '',
        string $comment = ''
    ): array {
        try {
            $client = $this->connect();

            if (empty($profile)) {
                $profile = config('mikrotik.default_profile', 'hspotcp');
            }

            // Check if user already exists
            $existing = $client->query(
                (new Query('/ip/hotspot/user/print'))
                    ->where('name', $username)
            )->read();

            $data = [
                '=name'     => $username,
                '=password' => $password,
                '=profile'  => $profile,
                '=comment'  => $comment,
            ];

            if (!empty($mac)) {
                $data['=mac-address'] = strtoupper($mac);
            }

            if (!empty($existing)) {
                // Update existing user
                $id = $existing[0]['.id'];
                $query = new Query('/ip/hotspot/user/set');
                $query->equal('.id', $id);
                foreach ($data as $key => $value) {
                    $query->equal(ltrim($key, '='), $value);
                }
                $client->query($query)->read();
            } else {
                // Create new user
                $query = new Query('/ip/hotspot/user/add');
                foreach ($data as $key => $value) {
                    $query->equal(ltrim($key, '='), $value);
                }
                $client->query($query)->read();
            }

            return ['success' => true, 'error' => null];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Retrieve all active hotspot sessions.
     */
    public function getActiveUsers(): array
    {
        try {
            $client = $this->connect();
            $result = $client->query(new Query('/ip/hotspot/active/print'))->read();

            return array_map(function ($user) {
                return [
                    'id'          => $user['.id'] ?? null,
                    'user'        => $user['user'] ?? '',
                    'mac'         => $user['mac-address'] ?? '',
                    'ip'          => $user['address'] ?? '',
                    'uptime'      => $user['uptime'] ?? '0s',
                    'bytes_in'    => $user['bytes-in'] ?? 0,
                    'bytes_out'   => $user['bytes-out'] ?? 0,
                    'comment'     => $user['comment'] ?? '',
                ];
            }, $result);
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Kick / disconnect a client by MAC address.
     */
    public function kickUser(string $macAddress): array
    {
        try {
            $client = $this->connect();
            $mac = strtoupper($macAddress);

            $sessions = $client->query(
                (new Query('/ip/hotspot/active/print'))
                    ->where('mac-address', $mac)
            )->read();

            if (empty($sessions)) {
                return ['success' => false, 'error' => 'No active session found for MAC: ' . $mac];
            }

            foreach ($sessions as $session) {
                $query = new Query('/ip/hotspot/active/remove');
                $query->equal('.id', $session['.id']);
                $client->query($query)->read();
            }

            return ['success' => true, 'error' => null, 'kicked' => count($sessions)];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Retrieve router system health: CPU, memory, uptime.
     */
    public function getSystemHealth(): array
    {
        try {
            $client = $this->connect();

            $resources = $client->query(new Query('/system/resource/print'))->read();
            $health    = $client->query(new Query('/system/health/print'))->read();

            if (empty($resources)) {
                return ['error' => 'No resource data returned'];
            }

            $res = $resources[0];

            $totalMem = (int) ($res['total-memory'] ?? 0);
            $freeMem  = (int) ($res['free-memory'] ?? 0);
            $usedMem  = $totalMem - $freeMem;
            $memPct   = $totalMem > 0 ? round(($usedMem / $totalMem) * 100, 1) : 0;

            $totalHdd = (int) ($res['total-hdd-space'] ?? 0);
            $freeHdd  = (int) ($res['free-hdd-space'] ?? 0);

            return [
                'uptime'         => $res['uptime'] ?? 'unknown',
                'cpu_load'       => (int) ($res['cpu-load'] ?? 0),
                'cpu_count'      => (int) ($res['cpu-count'] ?? 1),
                'memory_total'   => $totalMem,
                'memory_free'    => $freeMem,
                'memory_used'    => $usedMem,
                'memory_pct'     => $memPct,
                'hdd_total'      => $totalHdd,
                'hdd_free'       => $freeHdd,
                'board_name'     => $res['board-name'] ?? '',
                'ros_version'    => $res['version'] ?? '',
                'architecture'   => $res['architecture-name'] ?? '',
                'health'         => $health,
                'error'          => null,
            ];
        } catch (Throwable $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get all hotspot user profiles defined on the router.
     */
    public function getHotspotProfiles(): array
    {
        try {
            $client = $this->connect();
            return $client->query(new Query('/ip/hotspot/user/profile/print'))->read();
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Remove a hotspot user by username.
     */
    public function removeUser(string $username): array
    {
        try {
            $client = $this->connect();

            $users = $client->query(
                (new Query('/ip/hotspot/user/print'))
                    ->where('name', $username)
            )->read();

            foreach ($users as $user) {
                $query = new Query('/ip/hotspot/user/remove');
                $query->equal('.id', $user['.id']);
                $client->query($query)->read();
            }

            return ['success' => true, 'removed' => count($users)];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    // IP Binding (Bypass MAC Whitelist & Blocked MAC)
    // ============================================================

    public function getIpBindings(): array
    {
        try {
            $client = $this->connect();
            return $client->query(new Query('/ip/hotspot/ip-binding/print'))->read();
        } catch (Throwable $e) {
            return [];
        }
    }

    public function syncIpBinding(string $mac, string $type = 'bypassed', string $comment = '', string $address = ''): array
    {
        try {
            $client = $this->connect();
            $mac = strtoupper(trim($mac));

            $existing = $client->query(
                (new Query('/ip/hotspot/ip-binding/print'))->where('mac-address', $mac)
            )->read();

            $params = [
                '=mac-address' => $mac,
                '=type'        => $type,
                '=comment'     => $comment,
            ];
            if (!empty($address)) {
                $params['=address'] = $address;
            }

            if (!empty($existing)) {
                $id = $existing[0]['.id'];
                $query = new Query('/ip/hotspot/ip-binding/set');
                $query->equal('.id', $id);
                foreach ($params as $k => $v) {
                    $query->equal(ltrim($k, '='), $v);
                }
                $client->query($query)->read();
                return ['success' => true, 'action' => 'updated', 'id' => $id];
            } else {
                $query = new Query('/ip/hotspot/ip-binding/add');
                foreach ($params as $k => $v) {
                    $query->equal(ltrim($k, '='), $v);
                }
                $res = $client->query($query)->read();
                return ['success' => true, 'action' => 'created', 'id' => $res['after']['ret'] ?? null];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function removeIpBinding(string $mac): array
    {
        try {
            $client = $this->connect();
            $mac = strtoupper(trim($mac));

            $existing = $client->query(
                (new Query('/ip/hotspot/ip-binding/print'))->where('mac-address', $mac)
            )->read();

            foreach ($existing as $item) {
                $query = new Query('/ip/hotspot/ip-binding/remove');
                $query->equal('.id', $item['.id']);
                $client->query($query)->read();
            }

            return ['success' => true, 'removed' => count($existing)];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ============================================================
    // Hotspot User Profiles (QoS & Bandwidth Shaping)
    // ============================================================

    public function createOrUpdateHotspotProfile(array $data): array
    {
        try {
            $client = $this->connect();
            $name = $data['name'];

            $existing = $client->query(
                (new Query('/ip/hotspot/user/profile/print'))->where('name', $name)
            )->read();

            $params = [
                '=name'                => $name,
                '=rate-limit'          => $data['rate_limit'] ?? '5M/10M',
                '=shared-users'        => (string) ($data['shared_users'] ?? 1),
                '=status-autorefresh'  => $data['status_autorefresh'] ?? '1m',
            ];

            if (!empty($data['session_timeout'])) {
                $params['=session-timeout'] = $data['session_timeout'] . 's';
            }
            if (!empty($data['idle_timeout'])) {
                $params['=idle-timeout'] = $data['idle_timeout'] . 's';
            }
            if (!empty($data['keepalive_timeout'])) {
                $params['=keepalive-timeout'] = $data['keepalive_timeout'] . 's';
            }

            if (!empty($existing)) {
                $id = $existing[0]['.id'];
                $query = new Query('/ip/hotspot/user/profile/set');
                $query->equal('.id', $id);
                foreach ($params as $k => $v) {
                    $query->equal(ltrim($k, '='), $v);
                }
                $client->query($query)->read();
                return ['success' => true, 'action' => 'updated', 'id' => $id];
            } else {
                $query = new Query('/ip/hotspot/user/profile/add');
                foreach ($params as $k => $v) {
                    $query->equal(ltrim($k, '='), $v);
                }
                $res = $client->query($query)->read();
                return ['success' => true, 'action' => 'created', 'id' => $res['after']['ret'] ?? null];
            }
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function removeHotspotProfile(string $name): array
    {
        try {
            $client = $this->connect();
            $existing = $client->query(
                (new Query('/ip/hotspot/user/profile/print'))->where('name', $name)
            )->read();

            foreach ($existing as $item) {
                $query = new Query('/ip/hotspot/user/profile/remove');
                $query->equal('.id', $item['.id']);
                $client->query($query)->read();
            }

            return ['success' => true, 'removed' => count($existing)];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get system resource & hardware telemetry (/system/resource/print).
     *
     * @return array
     */
    public function getSystemResources(): array
    {
        try {
            $client = $this->connect();
            $res = $client->query(new Query('/system/resource/print'))->read();

            if (!empty($res[0])) {
                $raw = $res[0];
                $totalMem = (int) ($raw['total-memory'] ?? 0);
                $freeMem = (int) ($raw['free-memory'] ?? 0);
                $usedMem = max(0, $totalMem - $freeMem);
                $memPercent = $totalMem > 0 ? round(($usedMem / $totalMem) * 100, 1) : 0;

                $totalHdd = (int) ($raw['total-hdd-space'] ?? 0);
                $freeHdd = (int) ($raw['free-hdd-space'] ?? 0);
                $usedHdd = max(0, $totalHdd - $freeHdd);
                $hddPercent = $totalHdd > 0 ? round(($usedHdd / $totalHdd) * 100, 1) : 0;

                return [
                    'online'            => true,
                    'cpu_load'          => (int) ($raw['cpu-load'] ?? 0),
                    'cpu_count'         => (int) ($raw['cpu-count'] ?? 1),
                    'cpu_frequency'     => ($raw['cpu-frequency'] ?? '716') . ' MHz',
                    'uptime'            => $raw['uptime'] ?? '0s',
                    'version'           => $raw['version'] ?? 'RouterOS',
                    'board_name'        => $raw['board-name'] ?? 'MikroTik Router',
                    'architecture'      => $raw['architecture-name'] ?? 'arm',
                    'bad_blocks'        => $raw['bad-blocks'] ?? '0.0%',
                    'memory_total'      => $totalMem,
                    'memory_used'       => $usedMem,
                    'memory_percent'    => $memPercent,
                    'hdd_total'         => $totalHdd,
                    'hdd_used'          => $usedHdd,
                    'hdd_percent'       => $hddPercent,
                ];
            }
        } catch (Throwable $e) {
            // Unreachable
        }

        return [
            'online'            => false,
            'cpu_load'          => 0,
            'cpu_count'         => 1,
            'cpu_frequency'     => '—',
            'uptime'            => 'Offline',
            'version'           => 'RouterOS Offline',
            'board_name'        => 'Edge Router Unreachable',
            'architecture'      => '—',
            'bad_blocks'        => '0.0%',
            'memory_total'      => 0,
            'memory_used'       => 0,
            'memory_percent'    => 0,
            'hdd_total'         => 0,
            'hdd_used'          => 0,
            'hdd_percent'       => 0,
        ];
    }
}
