<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\RouterUserQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RouterSyncController extends Controller
{
    /**
     * Generate RouterOS .rsc script for MikroTik /tool fetch scheduler.
     * Consumed by router without needing any VPN tunnel.
     */
    public function syncScript(Request $request, string $siteSlug): Response
    {
        $site = Location::where(function ($q) use ($siteSlug) {
            $q->where('slug', $siteSlug)
              ->orWhere('id', $siteSlug);
        })->where('is_active', true)->first();

        if (!$site) {
            return response("# ERROR: Site '{$siteSlug}' not found or inactive in WiFiPads Cloud.\r\n", 404, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        // Enforce Secret Key Verification (Secure by default with timing-safe comparison)
        $expectedKey = $site->radius_secret;
        $providedKey = $request->header('X-Router-Key') ?? $request->bearerToken() ?? $request->query('key', '');

        if (empty($expectedKey) || empty($providedKey) || !hash_equals($expectedKey, (string) $providedKey)) {
            return response("# ERROR: Unauthorized. Invalid or missing secret key for site '{$site->name}'.\r\n", 401, [
                'Content-Type' => 'text/plain; charset=utf-8',
            ]);
        }

        // Fetch up to 100 pending users
        $pendingUsers = RouterUserQueue::where('location_id', $site->id)
            ->where('is_synced', false)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        $timestamp = now()->format('Y-m-d H:i:s');
        $lines = [];
        $lines[] = "# ========================================================";
        $lines[] = "# WiFiPads Cloud Edge Controller — User Sync Script";
        $lines[] = "# Site: {$site->name} ({$site->slug})";
        $lines[] = "# Timestamp: {$timestamp}";
        $lines[] = "# Pending Users: " . $pendingUsers->count();
        $lines[] = "# ========================================================";
        $lines[] = "";

        if ($pendingUsers->isEmpty()) {
            $lines[] = "# Info: Tidak ada antrean user baru saat ini.";
            $lines[] = ":log debug \"WiFiPads: Queue is empty for {$site->slug}\"";
            return response(implode("\r\n", $lines) . "\r\n", 200, [
                'Content-Type'        => 'text/plain; charset=utf-8',
                'Content-Disposition' => 'inline; filename="wifipads-sync.rsc"',
            ]);
        }

        $lines[] = "/ip hotspot user";

        foreach ($pendingUsers as $item) {
            $profile  = addcslashes($item->profile ?: 'default', "\"\\\r\n");
            $uptime   = addcslashes($item->limit_uptime ?: '02:00:00', "\"\\\r\n");
            $comment  = addcslashes($item->comment ?: 'wifipads-auto', "\"\\\r\n");
            $safeUser = addcslashes($item->username, "\"\\\r\n");
            $safePass = addcslashes($item->password, "\"\\\r\n");
            $safeMac  = $item->mac_address ? addcslashes($item->mac_address, "\"\\\r\n") : '';
            $macAttr  = $safeMac ? " mac-address=\"{$safeMac}\"" : "";

            // Idempotent: use :do { add ... } on-error={ set ... } to avoid script breakages
            $lines[] = ":do { add name=\"{$safeUser}\" password=\"{$safePass}\" profile=\"{$profile}\"{$macAttr} comment=\"{$comment}\" limit-uptime={$uptime} } on-error={ :do { set [find name=\"{$safeUser}\"] password=\"{$safePass}\" profile=\"{$profile}\"{$macAttr} limit-uptime={$uptime} } on-error={ :nothing } }";

            // Mark as synced
            $item->update([
                'is_synced' => true,
                'synced_at' => now(),
            ]);
        }

        $lines[] = "";
        $lines[] = ":log info \"WiFiPads: Successfully synced " . $pendingUsers->count() . " hotspot user(s) at {$timestamp}\"";
        $lines[] = "# End of Script";

        return response(implode("\r\n", $lines) . "\r\n", 200, [
            'Content-Type'        => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'inline; filename="wifipads-sync.rsc"',
        ]);
    }

    /**
     * Get queue monitoring stats for a site.
     */
    public function syncStatus(Request $request, string $siteSlug): JsonResponse
    {
        $site = Location::where(function ($q) use ($siteSlug) {
            $q->where('slug', $siteSlug)
              ->orWhere('id', $siteSlug);
        })->firstOrFail();

        $expectedKey = $site->radius_secret;
        $providedKey = $request->header('X-Router-Key') ?? $request->bearerToken() ?? $request->query('key', '');

        $isAuth = auth('web')->check() || auth()->check();
        $hasKey = !empty($expectedKey) && !empty($providedKey) && hash_equals($expectedKey, (string) $providedKey);

        if (!$isAuth && !$hasKey) {
            return response()->json(['error' => 'Unauthorized access'], 401);
        }

        $totalPending = RouterUserQueue::where('location_id', $site->id)->where('is_synced', false)->count();
        $totalSynced  = RouterUserQueue::where('location_id', $site->id)->where('is_synced', true)->count();
        $lastSynced   = RouterUserQueue::where('location_id', $site->id)->where('is_synced', true)->latest('synced_at')->first();

        return response()->json([
            'site'          => $site->name,
            'slug'          => $site->slug,
            'pending_users' => $totalPending,
            'synced_users'  => $totalSynced,
            'last_synced'   => $lastSynced?->synced_at?->toIso8601String(),
        ]);
    }
}
