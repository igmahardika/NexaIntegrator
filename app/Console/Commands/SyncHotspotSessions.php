<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\PortalSession;
use App\Services\MikrotikService;
use Illuminate\Console\Command;

class SyncHotspotSessions extends Command
{
    protected $signature = 'hotspot:sync-sessions {--location= : Specific location ID}';
    protected $description = 'Synchronize active router sessions, update traffic counters, and purge dead/zombie sessions';

    public function handle(): int
    {
        $this->info('Starting Hotspot Session Watchdog & Telemetry Sync...');

        $query = Location::where('is_active', true)->whereNotNull('router_ip');
        if ($locId = $this->option('location')) {
            $query->where('id', $locId);
        }

        $locations = $query->get();
        $totalPurged = 0;
        $totalUpdated = 0;

        foreach ($locations as $site) {
            $this->line("Checking site: <fg=cyan>{$site->name}</> ({$site->router_ip})...");

            try {
                $mikrotik = new MikrotikService($site);
                $activeUsers = $mikrotik->getActiveUsers();

                // Map active users by MAC
                $routerActiveMacs = [];
                foreach ($activeUsers as $u) {
                    if (!empty($u['mac'])) {
                        $mac = strtoupper($u['mac']);
                        $routerActiveMacs[$mac] = $u;
                    }
                }

                // 1. Update live traffic bytes for active sessions
                $dbActiveSessions = PortalSession::where('location_id', $site->id)
                    ->where('status', 'active')
                    ->get();

                foreach ($dbActiveSessions as $session) {
                    $mac = strtoupper($session->client_mac);

                    if (isset($routerActiveMacs[$mac])) {
                        $routerData = $routerActiveMacs[$mac];
                        $session->update([
                            'bytes_in'         => (int) ($routerData['bytes_in'] ?? $session->bytes_in),
                            'bytes_out'        => (int) ($routerData['bytes_out'] ?? $session->bytes_out),
                            'last_activity_at' => now(),
                        ]);
                        $totalUpdated++;
                    } else {
                        // Dead/Zombie session: User no longer in router active table
                        // Give 3 minutes grace period
                        if ($session->login_time && $session->login_time->diffInMinutes(now()) >= 3) {
                            $session->update([
                                'status'      => 'disconnected',
                                'logout_time' => now(),
                            ]);
                            $totalPurged++;
                            $this->line("  - Purged dead session for MAC: <fg=yellow>{$mac}</>");
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->warn("  Router unreachable for {$site->name}: " . $e->getMessage());
            }
        }

        $this->info("Complete! Updated traffic for {$totalUpdated} sessions. Purged {$totalPurged} zombie sessions.");
        return Command::SUCCESS;
    }
}
