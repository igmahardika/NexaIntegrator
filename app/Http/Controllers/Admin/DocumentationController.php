<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentationController extends Controller
{
    /**
     * Display the developer documentation and integration hub.
     */
    public function index(Request $request): View
    {
        $tenantLocationId = session('active_site_id');
        $currentLocation = $tenantLocationId
            ? Location::find($tenantLocationId)
            : Location::where('is_active', true)->first();

        $locations = Location::where('is_active', true)->get();
        $serverHost = $request->getHost();
        $serverPort = $request->getPort();
        $scheme = $request->getScheme();
        if (in_array($serverHost, ['localhost', '127.0.0.1']) && !empty($currentLocation?->radius_server_ip)) {
            $serverHost = $currentLocation->radius_server_ip;
            $portSuffix = ($serverPort && !in_array($serverPort, [80, 443])) ? ":{$serverPort}" : "";
            $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";
        } else {
            $baseUrl = $request->getSchemeAndHttpHost();
        }

        $activeTab = $request->query('tab', 'pms');
        if (!in_array($activeTab, ['pms', 'mikrotik', 'step-by-step'])) {
            $activeTab = 'pms';
        }

        return view('admin.docs.index', compact(
            'currentLocation',
            'locations',
            'serverHost',
            'serverPort',
            'baseUrl',
            'activeTab'
        ));
    }

    /**
     * Display a clean, printer-optimized document view for instant PDF generation.
     */
    public function printDoc(Request $request, string $topic): View
    {
        $tenantLocationId = session('active_site_id');
        $currentLocation = $tenantLocationId
            ? Location::find($tenantLocationId)
            : Location::where('is_active', true)->first();

        $serverHost = $request->getHost();
        $serverPort = $request->getPort();
        $scheme = $request->getScheme();
        if (in_array($serverHost, ['localhost', '127.0.0.1']) && !empty($currentLocation?->radius_server_ip)) {
            $serverHost = $currentLocation->radius_server_ip;
            $portSuffix = ($serverPort && !in_array($serverPort, [80, 443])) ? ":{$serverPort}" : "";
            $baseUrl = "{$scheme}://{$serverHost}{$portSuffix}";
        } else {
            $baseUrl = $request->getSchemeAndHttpHost();
        }

        if (!in_array($topic, ['pms', 'mikrotik', 'step-by-step', 'all'])) {
            $topic = 'all';
        }

        return view('admin.docs.print', compact(
            'currentLocation',
            'serverHost',
            'baseUrl',
            'topic'
        ));
    }
}
