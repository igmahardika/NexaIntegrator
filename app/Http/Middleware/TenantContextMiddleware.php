<?php

namespace App\Http\Middleware;

use App\Models\Location;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class TenantContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $availableTenantSites = Location::where('is_active', true)->orderBy('name')->get();
        $activeSiteId = session('active_site_id');
        $currentTenantSite = null;

        if ($activeSiteId === 'all') {
            // Explicit Global NOC Mode: All Sites
            $currentTenantSite = null;
            \App\Services\TenantManager::switchConnection(null);
        } elseif (!empty($activeSiteId)) {
            $currentTenantSite = $availableTenantSites->firstWhere('id', $activeSiteId);
            if ($currentTenantSite) {
                \App\Services\TenantManager::switchConnection($currentTenantSite);
            } else {
                // If ID is stale/not found, default to All Sites
                session(['active_site_id' => 'all']);
                \App\Services\TenantManager::switchConnection(null);
            }
        } else {
            // Default when session has no active_site_id: default to All Sites
            session(['active_site_id' => 'all']);
            $currentTenantSite = null;
            \App\Services\TenantManager::switchConnection(null);
        }

        // Share with all Blade views
        View::share('currentTenantSite', $currentTenantSite);
        View::share('availableTenantSites', $availableTenantSites);

        return $next($request);
    }
}
