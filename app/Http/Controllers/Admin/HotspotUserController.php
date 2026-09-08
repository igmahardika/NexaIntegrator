<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant\HotspotProfile;
use App\Models\Tenant\HotspotSession;
use App\Models\Tenant\HotspotUser;
use App\Services\RadiusService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HotspotUserController extends Controller
{
    /**
     * Display unified Hotspot Users listing.
     */
    public function index(Request $request): View
    {
        $currentSite = TenantManager::getActiveSite();
        $query = HotspotUser::with('profile');

        // Filter by auth_method / tab
        $tab = $request->query('tab', 'all');
        if ($tab === 'voucher') {
            $query->where('auth_method', HotspotUser::AUTH_VOUCHER);
        } elseif ($tab === 'member') {
            $query->where('auth_method', HotspotUser::AUTH_MEMBER);
        } elseif ($tab === 'leads') {
            $query->whereIn('auth_method', [HotspotUser::AUTH_WHATSAPP, HotspotUser::AUTH_SURVEY, HotspotUser::AUTH_QUICK]);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search by identifier, batch, or metadata
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('identifier', 'like', "%{$search}%")
                  ->orWhere('batch_name', 'like', "%{$search}%")
                  ->orWhere('guest_metadata', 'like', "%{$search}%");
            });
        }

        // Batch filter
        if ($request->filled('batch')) {
            $query->where('batch_name', $request->batch);
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        // Statistics
        $stats = [
            'total'    => HotspotUser::count(),
            'vouchers' => HotspotUser::where('auth_method', HotspotUser::AUTH_VOUCHER)->count(),
            'members'  => HotspotUser::where('auth_method', HotspotUser::AUTH_MEMBER)->count(),
            'leads'    => HotspotUser::whereIn('auth_method', [HotspotUser::AUTH_WHATSAPP, HotspotUser::AUTH_SURVEY, HotspotUser::AUTH_QUICK])->count(),
            'active'   => HotspotUser::where('status', HotspotUser::STATUS_ACTIVE)->count(),
            'ready'    => HotspotUser::where('status', HotspotUser::STATUS_READY)->count(),
        ];

        // Profiles & Batches for dropdowns
        $profiles = HotspotProfile::orderBy('name')->get();
        $batches  = HotspotUser::whereNotNull('batch_name')
            ->distinct()
            ->pluck('batch_name')
            ->filter();

        // Active template methods on current site
        $enabledMethods = $currentSite?->template_config['enabled_methods'] ?? ['voucher', 'member', 'whatsapp', 'quick_click'];

        return view('admin.hotspot_users.index', compact(
            'users',
            'stats',
            'profiles',
            'batches',
            'tab',
            'currentSite',
            'enabledMethods'
        ));
    }

    /**
     * Generate a batch of vouchers.
     */
    public function generateVouchers(Request $request): RedirectResponse
    {
        $request->validate([
            'quantity'         => 'required|integer|min:1|max:500',
            'profile_id'       => 'nullable|string',
            'code_length'      => 'required|integer|min:4|max:12',
            'prefix'           => 'nullable|string|max:8',
            'batch_name'       => 'nullable|string|max:50',
            'uptime_limit_hrs' => 'nullable|numeric|min:0.1|max:720',
            'data_limit_mb'    => 'nullable|integer|min:10|max:1000000',
        ]);

        $uptimeLimit = $request->filled('uptime_limit_hrs') ? (int) round($request->uptime_limit_hrs * 3600) : null;
        $dataLimit   = $request->filled('data_limit_mb') ? (int) ($request->data_limit_mb * 1048576) : null;
        $prefix      = strtoupper(trim((string) $request->prefix));
        $batchName   = trim((string) $request->batch_name) ?: 'Batch-' . now()->format('Ymd-Hi');

        $result = HotspotUser::generateBatch(
            quantity:       (int) $request->quantity,
            profileId:      $request->profile_id ?: null,
            codeLength:     (int) $request->code_length,
            prefix:         $prefix,
            batchName:      $batchName,
            uptimeLimit:    $uptimeLimit,
            dataLimitBytes: $dataLimit
        );

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'voucher', 'batch' => $batchName])
            ->with('success', "Berhasil men-generate {$result['count']} voucher pada batch \"{$batchName}\".");
    }

    /**
     * Create a permanent/member account.
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $request->validate([
            'username'         => 'required|string|min:3|max:50',
            'password'         => 'required|string|min:4',
            'profile_id'       => 'nullable|string',
            'name'             => 'nullable|string|max:100',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'expires_at'       => 'nullable|date',
        ]);

        if (HotspotUser::where('identifier', $request->username)->exists()) {
            return redirect()->back()->withErrors(['username' => 'Username tersebut sudah terdaftar di site ini.'])->withInput();
        }

        HotspotUser::create([
            'identifier'       => trim($request->username),
            'secret'           => bcrypt($request->password),
            'auth_method'      => HotspotUser::AUTH_MEMBER,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => $request->simultaneous_use ?: 1,
            'expires_at'       => $request->expires_at ? now()->parse($request->expires_at) : null,
            'guest_metadata'   => [
                'full_name' => $request->name,
                'created_by'=> auth()->user()?->name ?? 'Admin',
            ],
        ]);

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'member'])
            ->with('success', "Akun Member \"{$request->username}\" berhasil dibuat.");
    }

    /**
     * Toggle active/disabled status.
     */
    public function toggleStatus(HotspotUser $user): RedirectResponse
    {
        $newStatus = $user->status === HotspotUser::STATUS_DISABLED ? HotspotUser::STATUS_READY : HotspotUser::STATUS_DISABLED;
        $user->update(['status' => $newStatus]);

        $msg = $newStatus === HotspotUser::STATUS_DISABLED ? 'dinonaktifkan' : 'diaktifkan kembali';
        return redirect()->back()->with('success', "User \"{$user->identifier}\" berhasil {$msg}.");
    }

    /**
     * Delete user record.
     */
    public function destroy(HotspotUser $user): RedirectResponse
    {
        $identifier = $user->identifier;
        $user->delete();

        return redirect()->back()->with('success', "User \"{$identifier}\" berhasil dihapus.");
    }

    /**
     * Delete an entire batch of vouchers.
     */
    public function destroyBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'batch_name' => 'required|string',
        ]);

        $deleted = HotspotUser::where('batch_name', $request->batch_name)->delete();

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'voucher'])
            ->with('success', "Sebanyak {$deleted} voucher dalam batch \"{$request->batch_name}\" berhasil dihapus.");
    }

    /**
     * Printable view for a voucher batch.
     */
    public function printBatch(string $batchName): View
    {
        $currentSite = TenantManager::getActiveSite();
        $vouchers = HotspotUser::where('batch_name', $batchName)
            ->with('profile')
            ->orderBy('created_at')
            ->get();

        if ($vouchers->isEmpty()) {
            abort(404, 'Batch voucher tidak ditemukan.');
        }

        return view('admin.hotspot_users.print_batch', compact('vouchers', 'batchName', 'currentSite'));
    }

    /**
     * Kick an active guest session.
     */
    public function kickSession(string $sessionId): RedirectResponse
    {
        $session = HotspotSession::findOrFail($sessionId);
        $currentSite = TenantManager::getActiveSite();

        if ($currentSite && $currentSite->radius_secret) {
            $radius = new RadiusService($currentSite);
            $radius->sendDisconnect($session->mac_address, $session->ip_address ?: '', $session->username);
        }

        $session->closeSession('admin_kick');

        return redirect()->back()->with('success', "Sesi untuk {$session->username} ({$session->mac_address}) berhasil diputuskan.");
    }
}
