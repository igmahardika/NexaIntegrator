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

        if (!empty($activeSiteId)) {
            $currentTenantSite = $availableTenantSites->firstWhere('id', $activeSiteId);
        }

        // If no active site selected or invalid, default to the first available site
        if (!$currentTenantSite && $availableTenantSites->isNotEmpty()) {
            $currentTenantSite = $availableTenantSites->first();
            session(['active_site_id' => $currentTenantSite->id]);
        }

        if ($currentTenantSite) {
            \App\Services\TenantManager::switchConnection($currentTenantSite);
        }

        // Share with all Blade views
        View::share('currentTenantSite', $currentTenantSite);
        View::share('availableTenantSites', $availableTenantSites);

        return $next($request);
    }
}
