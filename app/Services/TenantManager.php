<?php

namespace App\Services;

use App\Models\Location;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class TenantManager
{
    protected static ?Location $activeSite = null;

    /**
     * Determine if MySQL driver is active for database operations.
     */
    public static function isMysql(): bool
    {
        return config('database.default') === 'mysql'
            || config('database.connections.tenant.driver') === 'mysql';
    }

    /**
     * Get the tenants storage directory path (used for SQLite driver).
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
     * Get the database name for MySQL or absolute file path for SQLite.
     *
     * @param \App\Models\Location|string $site
     */
    public static function getDatabaseIdentifier($site): string
    {
        $siteId = $site instanceof Location ? $site->id : $site;

        if (self::isMysql()) {
            return "wifipads_site_{$siteId}";
        }

        $dir = self::getTenantsDirectory();
        return $dir . DIRECTORY_SEPARATOR . "site_{$siteId}.sqlite";
    }

    /**
     * Legacy helper for SQLite path.
     *
     * @param \App\Models\Location|string $site
     */
    public static function getDatabasePath($site): string
    {
        return self::getDatabaseIdentifier($site);
    }

    /**
     * Ensure the tenant database exists and run tenant migrations if newly created.
     *
     * @param \App\Models\Location|string $site
     */
    public static function ensureDatabase($site): string
    {
        $identifier = self::getDatabaseIdentifier($site);

        if (self::isMysql()) {
            self::ensureMysqlDatabase($identifier);
        } else {
            self::ensureSqliteDatabase($identifier);
        }

        return $identifier;
    }

    /**
     * Ensure SQLite file exists and migrate.
     */
    protected static function ensureSqliteDatabase(string $dbPath): void
    {
        $isNew = !File::exists($dbPath);

        if ($isNew) {
            touch($dbPath);
            self::migrateTenantDatabase($dbPath);
        }
    }

    /**
     * Ensure MySQL database exists and migrate.
     */
    protected static function ensureMysqlDatabase(string $dbName): void
    {
        try {
            // Execute CREATE DATABASE IF NOT EXISTS via default connection
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            // Log or fallback if user lacks CREATE DATABASE privileges
        }

        // Check if tenant tables need migration
        config(['database.connections.tenant.database' => $dbName]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        try {
            $hasHotspotUsers = DB::connection('tenant')->getSchemaBuilder()->hasTable('hotspot_users');
            if (!$hasHotspotUsers) {
                self::migrateTenantDatabase($dbName);
            }
        } catch (Throwable $e) {
            self::migrateTenantDatabase($dbName);
        }
    }

    /**
     * Run tenant migrations on the specified database.
     */
    public static function migrateTenantDatabase(string $database): void
    {
        config(['database.connections.tenant.database' => $database]);
        DB::purge('tenant');
        DB::reconnect('tenant');

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant',
            '--force'    => true,
        ]);
    }

    /**
     * Ensure the default fallback tenant database exists and is migrated.
     */
    public static function ensureDefaultDatabase(): string
    {
        if (self::isMysql()) {
            self::ensureMysqlDatabase('wifipads_site_default');
            return 'wifipads_site_default';
        }

        $dir = self::getTenantsDirectory();
        $defaultPath = $dir . DIRECTORY_SEPARATOR . 'default.sqlite';
        self::ensureSqliteDatabase($defaultPath);
        return $defaultPath;
    }

    /**
     * Switch the active tenant database connection dynamically.
     *
     * @param \App\Models\Location|string|null $site
     */
    public static function switchConnection($site): void
    {
        if (empty($site)) {
            self::$activeSite = null;
            $defaultDb = self::ensureDefaultDatabase();
            config(['database.connections.tenant.database' => $defaultDb]);
            DB::purge('tenant');
            DB::reconnect('tenant');
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
        $db = self::ensureDatabase($site);

        config(['database.connections.tenant.database' => $db]);
        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    /**
     * Get the current active Site.
     */
    public static function getActiveSite(): ?Location
    {
        $siteId = session('active_site_id');
        if ($siteId === 'all') {
            self::$activeSite = null;
            return null;
        }

        if (self::$activeSite && (empty($siteId) || self::$activeSite->id === $siteId)) {
            return self::$activeSite;
        }

        if (!empty($siteId)) {
            self::$activeSite = Location::find($siteId);
            if (self::$activeSite) {
                self::switchConnection(self::$activeSite);
            }
        }

        return self::$activeSite;
    }

    /**
     * Run a callback across all active tenant databases and return collected results.
     */
    public static function runOnAllTenants(callable $callback): array
    {
        $sites = Location::where('is_active', true)->get();
        $results = [];

        $previousSite = self::$activeSite;

        foreach ($sites as $site) {
            self::switchConnection($site);
            try {
                $results[$site->id] = $callback($site);
            } catch (Throwable $e) {
                $results[$site->id] = null;
            }
        }

        // Restore previous connection
        if ($previousSite) {
            self::switchConnection($previousSite);
        } else {
            self::switchConnection(null);
        }

        return $results;
    }
}
