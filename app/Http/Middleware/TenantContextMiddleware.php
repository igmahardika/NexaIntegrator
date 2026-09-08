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
        $activeSiteId = session('active_site_id');
        $currentTenantSite = null;

        if (!empty($activeSiteId) && $activeSiteId !== 'all') {
            $currentTenantSite = Location::find($activeSiteId);
        }

        $availableTenantSites = Location::orderBy('name')->get();

        // Share with all Blade views
        View::share('currentTenantSite', $currentTenantSite);
        View::share('availableTenantSites', $availableTenantSites);

        return $next($request);
    }
}
