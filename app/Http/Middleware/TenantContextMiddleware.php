<?php

namespace App\Http\Middleware;

use App\Models\Location;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class TenantContextMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 1. Strict Scoping for Non-Superadmin Operators / Cashiers
        if ($user && !$user->isSuperadmin()) {
            if (empty($user->site_id)) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Akun Anda belum diasosiasikan ke Site manapun. Hubungi Superadmin.');
            }

            $currentTenantSite = Location::where('id', $user->site_id)->where('is_active', true)->first();
            if (!$currentTenantSite) {
                auth()->logout();
                return redirect()->route('login')->with('error', 'Site yang diasosiasikan ke akun Anda sedang tidak aktif.');
            }

            // Lock session and available sites strictly to the assigned site
            session(['active_site_id' => $currentTenantSite->id]);
            $availableTenantSites = collect([$currentTenantSite]);
            TenantManager::switchConnection($currentTenantSite);

            View::share('currentTenantSite', $currentTenantSite);
            View::share('availableTenantSites', $availableTenantSites);

            return $next($request);
        }

        // 2. Global NOC / Superadmin Multi-Site Scope
        try {
            $availableTenantSites = ($user && $user->isSuperadmin())
                ? Location::orderBy('name')->get()
                : Location::where('is_active', true)->orderBy('name')->get();
        } catch (\Throwable $e) {
            $availableTenantSites = collect();
        }
        $activeSiteId = session('active_site_id');
        $currentTenantSite = null;

        if ($activeSiteId === 'all') {
            // Explicit Global NOC Mode: All Sites
            $currentTenantSite = null;
            TenantManager::switchConnection(null);
        } elseif (!empty($activeSiteId)) {
            $currentTenantSite = $availableTenantSites->firstWhere('id', $activeSiteId);
            if ($currentTenantSite) {
                TenantManager::switchConnection($currentTenantSite);
            } else {
                // If ID is stale/not found, default to All Sites
                session(['active_site_id' => 'all']);
                TenantManager::switchConnection(null);
            }
        } else {
            // Default when session has no active_site_id: default to All Sites
            session(['active_site_id' => 'all']);
            $currentTenantSite = null;
            TenantManager::switchConnection(null);
        }

        // Share with all Blade views
        View::share('currentTenantSite', $currentTenantSite);
        View::share('availableTenantSites', $availableTenantSites);

        return $next($request);
    }
}
