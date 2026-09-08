<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class TenantManager
{
    protected static ?Location $activeSite = null;

    /**
     * Get the tenants storage directory path.
     */
    public static function getTenantsDirectory(): string
    {
        $dir = database_path('tenants');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Get the absolute path for a site's SQLite database.
     */
    public static function getDatabasePath(Location|string $site): string
    {
        $siteId = $site instanceof Location ? $site->id : $site;
        $dir = self::getTenantsDirectory();
        return $dir . DIRECTORY_SEPARATOR . "site_{$siteId}.sqlite";
    }

    /**
     * Ensure the tenant SQLite database exists and run tenant migrations if newly created.
     */
    public static function ensureDatabase(Location|string $site): string
    {
        $dbPath = self::getDatabasePath($site);
        $isNew = !File::exists($dbPath);

        if ($isNew) {
            touch($dbPath);
            self::migrateTenantDatabase($dbPath);
        }

        return $dbPath;
    }

    /**
     * Run tenant migrations on a specific SQLite database path.
     */
    public static function migrateTenantDatabase(string $dbPath): void
    {
        config(['database.connections.tenant.database' => $dbPath]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
    }

    /**
     * Switch the active tenant database connection dynamically.
     */
    public static function switchConnection(Location|string|null $site): void
    {
        if (empty($site)) {
            self::$activeSite = null;
            return;
        }

        if (is_string($site)) {
            $location = Location::find($site);
            if (!$location) {
                return;
            }
            $site = $location;
        }

        self::$activeSite = $site;
        $dbPath = self::ensureDatabase($site);

        config(['database.connections.tenant.database' => $dbPath]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Get the current active Site.
     */
    public static function getActiveSite(): ?Location
    {
        if (self::$activeSite) {
            return self::$activeSite;
        }

        $siteId = session('active_site_id');
        if (!empty($siteId) && $siteId !== 'all') {
            self::$activeSite = Location::find($siteId);
            if (self::$activeSite) {
                self::switchConnection(self::$activeSite);
            }
        }

        return self::$activeSite;
    }
}
