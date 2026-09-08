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
}
