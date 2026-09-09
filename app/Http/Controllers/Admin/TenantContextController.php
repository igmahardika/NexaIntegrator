<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class TenantContextController extends Controller
{
    /**
     * Switch the active tenant site context in session.
     */
    public function switch(Request $request)
    {
        $siteId = $request->input('site_id');

        if ($siteId === 'all' || empty($siteId)) {
            session(['active_site_id' => 'all']);
            \App\Services\TenantManager::switchConnection(null);
            return redirect()->back()->with('success', 'Beralih ke konteks Global (Semua Site).');
        }

        $site = Location::findOrFail($siteId);
        session(['active_site_id' => $site->id]);
        \App\Services\TenantManager::switchConnection($site);

        return redirect()->back()->with('success', "Konteks operasional beralih ke: {$site->name}");
    }

    /**
     * Live search sites for command palette / combobox.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));

        $sites = Location::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('customer_name', 'like', "%{$q}%")
                        ->orWhere('router_ip', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(50)
            ->get(['id', 'name', 'customer_name', 'business_type', 'router_ip', 'address', 'gateway_mode', 'is_active']);

        return response()->json($sites);
    }
}
