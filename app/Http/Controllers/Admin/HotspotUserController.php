<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IpBinding;
use App\Models\Location;
use App\Models\Tenant\HotspotProfile;
use App\Models\Tenant\HotspotSession;
use App\Models\Tenant\HotspotUser;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use App\Services\TenantManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class HotspotUserController extends Controller
{
    /**
     * Display unified Hotspot Users listing with multi-method filters.
     */
    public function index(Request $request): View
    {
        $availableSites = Location::where('is_active', true)->orderBy('name')->get();
        if ($availableSites->isEmpty()) {
            $availableSites = Location::orderBy('name')->get();
        }
        $currentSite = TenantManager::getActiveSite();

        // If in All Sites mode or no site selected, default to first site
        if (!$currentSite) {
            $targetSiteId = $request->query('site_id');
            $currentSite = $targetSiteId ? $availableSites->firstWhere('id', $targetSiteId) : $availableSites->first();
            if ($currentSite) {
                TenantManager::switchConnection($currentSite);
            }
        }

        // If still no site available (system has 0 locations)
        if (!$currentSite) {
            TenantManager::switchConnection(null);
            $users = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25);
            $stats = [
                'total'    => 0,
                'vouchers' => 0,
                'members'  => 0,
                'whatsapp' => 0,
                'mac'      => 0,
                'hotel'    => 0,
                'leads'    => 0,
                'active'   => 0,
                'ready'    => 0,
            ];
            $profiles = collect();
            $batches  = collect();
            $tab      = $request->query('tab', 'all');
            $enabledMethods = [];

            return view('admin.hotspot_users.index', compact(
                'users',
                'stats',
                'profiles',
                'batches',
                'tab',
                'currentSite',
                'availableSites',
                'enabledMethods'
            ));
        }

        $query = HotspotUser::with('profile');

        // Filter by auth_method / tab
        $tab = $request->query('tab', 'all');
        if ($tab === 'voucher') {
            $query->where('auth_method', HotspotUser::AUTH_VOUCHER);
        } elseif ($tab === 'member') {
            $query->where('auth_method', HotspotUser::AUTH_MEMBER);
        } elseif ($tab === 'whatsapp') {
            $query->where('auth_method', HotspotUser::AUTH_WHATSAPP);
        } elseif ($tab === 'mac') {
            $query->where('auth_method', HotspotUser::AUTH_MAC);
        } elseif ($tab === 'hotel') {
            $query->where('auth_method', HotspotUser::AUTH_PMS);
        } elseif ($tab === 'leads') {
            $query->whereIn('auth_method', [HotspotUser::AUTH_SURVEY, HotspotUser::AUTH_QUICK, HotspotUser::AUTH_EMAIL]);
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
                  ->orWhere('guest_metadata', 'like', "%{$search}%")
                  ->orWhere('bound_mac', 'like', "%{$search}%");
            });
        }

        // Batch filter
        if ($request->filled('batch')) {
            $query->where('batch_name', $request->batch);
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        // Statistics across all login methods
        $stats = [
            'total'    => HotspotUser::count(),
            'vouchers' => HotspotUser::where('auth_method', HotspotUser::AUTH_VOUCHER)->count(),
            'members'  => HotspotUser::where('auth_method', HotspotUser::AUTH_MEMBER)->count(),
            'whatsapp' => HotspotUser::where('auth_method', HotspotUser::AUTH_WHATSAPP)->count(),
            'mac'      => HotspotUser::where('auth_method', HotspotUser::AUTH_MAC)->count(),
            'hotel'    => HotspotUser::where('auth_method', HotspotUser::AUTH_PMS)->count(),
            'leads'    => HotspotUser::whereIn('auth_method', [HotspotUser::AUTH_SURVEY, HotspotUser::AUTH_QUICK, HotspotUser::AUTH_EMAIL])->count(),
            'active'   => HotspotUser::where('status', HotspotUser::STATUS_ACTIVE)->count(),
            'ready'    => HotspotUser::where('status', HotspotUser::STATUS_READY)->count(),
        ];

        // Ensure tenant DB is populated with Profiles from Central HotspotProfiles
        if ($currentSite) {
            $centralProfiles = \App\Models\HotspotProfile::where('location_id', $currentSite->id)->get();
            foreach ($centralProfiles as $cp) {
                HotspotProfile::updateOrCreate(
                    ['name' => $cp->name],
                    [
                        'rate_limit'   => $cp->rate_limit,
                        'shared_users' => $cp->shared_users,
                        'uptime_limit' => $cp->session_timeout ? ($cp->session_timeout * 60) : 7200,
                        'description'  => $cp->display_name ?? $cp->name,
                    ]
                );
            }
        }
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
            'availableSites',
            'enabledMethods'
        ));
    }

    /**
     * Ensure an active tenant connection is switched on.
     */
    protected function ensureActiveTenant(): ?Location
    {
        $currentSite = TenantManager::getActiveSite();
        if (!$currentSite) {
            $site = Location::where('is_active', true)->first() ?: Location::first();
            if ($site) {
                TenantManager::switchConnection($site);
                return $site;
            }
        }
        return $currentSite;
    }

    /**
     * 1. ACCESS CODE: Create a single custom Access Code.
     * Mapped to MikroTik: name, password, profile, limit-uptime, limit-bytes-total, shared-users, mac-address, comment.
     */
    public function storeAccessCode(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

        $request->validate([
            'code'             => 'required|string|min:3|max:30',
            'password'         => 'nullable|string|max:50',
            'profile_id'       => 'nullable|string',
            'uptime_limit_hrs' => 'nullable|numeric|min:0.1|max:720',
            'data_limit_mb'    => 'nullable|integer|min:10|max:1000000',
            'simultaneous_use' => 'nullable|integer|min:1|max:100',
            'bound_mac'        => ['nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'notes'            => 'nullable|string|max:100',
        ]);

        $code = strtoupper(trim($request->code));

        if (HotspotUser::where('identifier', $code)->exists()) {
            return redirect()->back()->withErrors(['code' => "Access Code \"{$code}\" sudah terdaftar di site ini."])->withInput();
        }

        $uptimeLimit = $request->filled('uptime_limit_hrs') ? (int) round($request->uptime_limit_hrs * 3600) : null;
        $dataLimit   = $request->filled('data_limit_mb') ? (int) ($request->data_limit_mb * 1048576) : null;
        $boundMac    = $request->filled('bound_mac') ? strtoupper(str_replace('-', ':', trim($request->bound_mac))) : null;
        $secret      = $request->filled('password') ? trim($request->password) : Str::random(12);

        HotspotUser::create([
            'identifier'       => $code,
            'secret'           => $secret,
            'auth_method'      => HotspotUser::AUTH_VOUCHER,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => $request->simultaneous_use ?: 1,
            'bound_mac'        => $boundMac,
            'uptime_limit'     => $uptimeLimit,
            'data_limit_bytes' => $dataLimit,
            'batch_name'       => 'Access Code',
            'guest_metadata'   => [
                'type'       => 'custom_access_code',
                'notes'      => $request->notes ?: 'Dibuat manual oleh Admin',
                'created_by' => auth()->user()?->name ?? 'Admin',
            ],
        ]);

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'voucher'])
            ->with('success', "Access Code \"{$code}\" berhasil dibuat dan siap digunakan pada portal login!");
    }

    /**
     * 2. VOUCHER BATCH: Generate a batch of vouchers.
     * Mapped to MikroTik: name, profile, limit-uptime, limit-bytes-total, comment (batch name).
     */
    public function generateVouchers(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

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
            ->with('success', "Berhasil men-generate {$result['count']} kode akses pada batch \"{$batchName}\".");
    }

    /**
     * 3. MEMBER: Create a permanent or staff account.
     * Mapped to MikroTik: name, password, profile, shared-users, mac-address, expires_at, comment.
     */
    public function storeMember(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

        $request->validate([
            'username'         => 'required|string|min:3|max:50',
            'password'         => 'required|string|min:4',
            'profile_id'       => 'nullable|string',
            'name'             => 'nullable|string|max:100',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'bound_mac'        => ['nullable', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'expires_at'       => 'nullable|date',
            'notes'            => 'nullable|string|max:100',
        ]);

        $username = trim($request->username);

        if (HotspotUser::where('identifier', $username)->exists()) {
            return redirect()->back()->withErrors(['username' => "Username \"{$username}\" sudah terdaftar di site ini."])->withInput();
        }

        $boundMac = $request->filled('bound_mac') ? strtoupper(str_replace('-', ':', trim($request->bound_mac))) : null;

        HotspotUser::create([
            'identifier'       => $username,
            'secret'           => bcrypt($request->password),
            'auth_method'      => HotspotUser::AUTH_MEMBER,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => $request->simultaneous_use ?: 1,
            'bound_mac'        => $boundMac,
            'expires_at'       => $request->expires_at ? now()->parse($request->expires_at)->endOfDay() : null,
            'batch_name'       => 'Member',
            'guest_metadata'   => [
                'full_name'  => $request->name,
                'notes'      => $request->notes,
                'created_by' => auth()->user()?->name ?? 'Admin',
            ],
        ]);

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'member'])
            ->with('success', "Akun Member \"{$username}\" berhasil dibuat.");
    }

    /**
     * 4. WHATSAPP GUEST: Pre-register a WhatsApp phone guest.
     * Mapped to MikroTik: name (phone), password (PIN/OTP), profile, limit-uptime, limit-bytes-total, comment.
     */
    public function storeWhatsapp(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

        $request->validate([
            'phone'            => 'required|string|min:8|max:25',
            'name'             => 'required|string|max:100',
            'password'         => 'nullable|string|max:30',
            'profile_id'       => 'nullable|string',
            'uptime_limit_hrs' => 'nullable|numeric|min:0.1|max:720',
            'data_limit_mb'    => 'nullable|integer|min:10|max:1000000',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'notes'            => 'nullable|string|max:150',
        ]);

        $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);

        if (HotspotUser::where('identifier', $cleanPhone)->exists()) {
            return redirect()->back()->withErrors(['phone' => "Nomor WhatsApp {$cleanPhone} sudah terdaftar di site ini."])->withInput();
        }

        $uptimeLimit = $request->filled('uptime_limit_hrs') ? (int) round($request->uptime_limit_hrs * 3600) : null;
        $dataLimit   = $request->filled('data_limit_mb') ? (int) ($request->data_limit_mb * 1048576) : null;
        $secret      = $request->filled('password') ? trim($request->password) : Str::random(6);

        HotspotUser::create([
            'identifier'       => $cleanPhone,
            'secret'           => $secret,
            'auth_method'      => HotspotUser::AUTH_WHATSAPP,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => $request->simultaneous_use ?: 1,
            'uptime_limit'     => $uptimeLimit,
            'data_limit_bytes' => $dataLimit,
            'batch_name'       => 'WhatsApp Guest',
            'guest_metadata'   => [
                'full_name'  => $request->name,
                'phone'      => $cleanPhone,
                'notes'      => $request->notes,
                'created_by' => auth()->user()?->name ?? 'Admin',
            ],
        ]);

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'whatsapp'])
            ->with('success', "Tamu WhatsApp {$request->name} ({$cleanPhone}) berhasil didaftarkan.");
    }

    /**
     * 5. MAC BYPASS: Device Whitelist (IoT, Smart TV, POS, Printer).
     * Mapped to MikroTik: name (MAC), mac-address (MAC), profile, IP Binding bypassed.
     */
    public function storeMacBypass(Request $request): RedirectResponse
    {
        $site = $this->ensureActiveTenant();

        $request->validate([
            'mac_address'      => ['required', 'string', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'device_name'      => 'required|string|max:100',
            'device_category'  => 'required|string|max:50',
            'profile_id'       => 'nullable|string',
            'uptime_limit_hrs' => 'nullable|numeric|min:0.1|max:8760',
            'notes'            => 'nullable|string|max:150',
        ]);

        $mac = strtoupper(str_replace('-', ':', trim($request->mac_address)));

        if (HotspotUser::where('identifier', $mac)->exists()) {
            return redirect()->back()->withErrors(['mac_address' => "MAC Address {$mac} sudah terdaftar di site ini."])->withInput();
        }

        $uptimeLimit = $request->filled('uptime_limit_hrs') ? (int) round($request->uptime_limit_hrs * 3600) : null;

        HotspotUser::create([
            'identifier'       => $mac,
            'secret'           => $mac,
            'auth_method'      => HotspotUser::AUTH_MAC,
            'bound_mac'        => $mac,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => 1,
            'uptime_limit'     => $uptimeLimit,
            'batch_name'       => 'MAC Whitelist',
            'guest_metadata'   => [
                'device_name'     => $request->device_name,
                'device_category' => $request->device_category,
                'notes'           => $request->notes,
                'created_by'      => auth()->user()?->name ?? 'Admin',
            ],
        ]);

        // Automatically register to IP Binding / RouterOS Walled Garden Layer-2 Bypass
        if ($site) {
            $comment = "{$request->device_name} (" . ($request->notes ?: $request->device_category) . ')';
            $binding = IpBinding::updateOrCreate(
                ['location_id' => $site->id, 'mac_address' => $mac],
                [
                    'type'            => 'bypassed',
                    'device_category' => $request->device_category,
                    'comment'         => $comment,
                ]
            );

            if (!empty($site->router_ip)) {
                try {
                    $mikrotik = new MikrotikService($site);
                    $res = $mikrotik->syncIpBinding($mac, 'bypassed', $comment);
                    if ($res['success'] ?? false) {
                        $binding->update(['synced_to_router' => true, 'router_binding_id' => $res['id'] ?? null]);
                    }
                } catch (Throwable $e) {
                    // Ignore offline router
                }
            }
        }

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'mac'])
            ->with('success', "Perangkat {$request->device_name} ({$mac}) berhasil di-whitelist (MAC Bypass).");
    }

    /**
     * 6. HOTEL ROOM (PMS): Room Guest account.
     * Mapped to MikroTik: name (Room Number), password (Last Name), profile, shared-users, expires_at (checkout).
     */
    public function storeHotelRoom(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

        $request->validate([
            'room_number'      => 'required|string|max:20',
            'last_name'        => 'required|string|max:50',
            'full_name'        => 'nullable|string|max:100',
            'profile_id'       => 'nullable|string',
            'simultaneous_use' => 'nullable|integer|min:1|max:10',
            'checkout_date'    => 'nullable|date',
            'notes'            => 'nullable|string|max:150',
        ]);

        $room = trim($request->room_number);
        $identifier = str_starts_with(strtoupper($room), 'ROOM-') ? strtoupper($room) : 'ROOM-' . strtoupper($room);
        $lastName = strtoupper(trim($request->last_name));

        if (HotspotUser::where('identifier', $identifier)->exists()) {
            return redirect()->back()->withErrors(['room_number' => "Kamar {$room} ({$identifier}) sudah aktif di site ini."])->withInput();
        }

        $expiresAt = $request->filled('checkout_date') ? now()->parse($request->checkout_date)->endOfDay() : null;

        HotspotUser::create([
            'identifier'       => $identifier,
            'secret'           => $lastName,
            'auth_method'      => HotspotUser::AUTH_PMS,
            'profile_id'       => $request->profile_id ?: null,
            'status'           => HotspotUser::STATUS_READY,
            'simultaneous_use' => $request->simultaneous_use ?: 4,
            'expires_at'       => $expiresAt,
            'batch_name'       => 'Hotel Room',
            'guest_metadata'   => [
                'room_number'      => $room,
                'guest_last_name'  => $lastName,
                'guest_full_name'  => $request->full_name,
                'notes'            => $request->notes,
                'created_by'       => auth()->user()?->name ?? 'Admin',
            ],
        ]);

        return redirect()->route('admin.hotspot-users.index', ['tab' => 'hotel'])
            ->with('success', "Akun Kamar {$room} (Tamu: {$lastName}) berhasil dibuat.");
    }

    /**
     * Toggle active/disabled status.
     */
    public function toggleStatus(HotspotUser $user): RedirectResponse
    {
        $newStatus = $user->status === HotspotUser::STATUS_DISABLED ? HotspotUser::STATUS_READY : HotspotUser::STATUS_DISABLED;
        $user->update(['status' => $newStatus]);

        $action = $newStatus === HotspotUser::STATUS_DISABLED ? 'dinonaktifkan' : 'diaktifkan kembali';
        return redirect()->back()->with('success', "User {$user->identifier} berhasil {$action}.");
    }

    /**
     * Permanently delete a hotspot user.
     */
    public function destroy(HotspotUser $user): RedirectResponse
    {
        $identifier = $user->identifier;
        $user->delete();

        return redirect()->back()->with('success', "User {$identifier} berhasil dihapus.");
    }

    /**
     * Bulk delete an entire batch of vouchers.
     */
    public function destroyBatch(Request $request): RedirectResponse
    {
        $this->ensureActiveTenant();

        $request->validate([
            'batch_name' => 'required|string',
        ]);

        $count = HotspotUser::where('batch_name', $request->batch_name)->delete();

        return redirect()->route('admin.hotspot-users.index')
            ->with('success', "Berhasil menghapus {$count} voucher pada batch \"{$request->batch_name}\".");
    }

    /**
     * Print vouchers by batch name in ticket card layout.
     */
    public function printBatch(string $batchName): View
    {
        $this->ensureActiveTenant();

        $users = HotspotUser::where('batch_name', $batchName)
            ->with('profile')
            ->get();

        $currentSite = TenantManager::getActiveSite();

        return view('admin.hotspot_users.print', compact('users', 'batchName', 'currentSite'));
    }

    /**
     * Force disconnect an active hotspot session.
     */
    public function kickSession(string $sessionId): RedirectResponse
    {
        $this->ensureActiveTenant();

        $session = HotspotSession::findOrFail($sessionId);
        $currentSite = TenantManager::getActiveSite();

        if ($currentSite) {
            $radius = new RadiusService($currentSite);
            $radius->sendDisconnect($session->mac_address, $session->ip_address ?? '', $session->username);
        }

        $session->update([
            'status'          => 'closed',
            'session_end'     => now(),
            'terminate_cause' => 'Admin Disconnect',
        ]);

        return redirect()->back()->with('success', "Sesi untuk {$session->username} ({$session->mac_address}) berhasil diputuskan.");
    }
}
