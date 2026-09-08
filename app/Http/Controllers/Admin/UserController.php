<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of operators and dashboard access accounts.
     */
    public function index(Request $request): View
    {
        $query = User::with('site');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($siteId = $request->input('site_id')) {
            $query->where('site_id', $siteId);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $sites = Location::where('is_active', true)->orderBy('name')->get();

        $roles = [
            'superadmin' => [
                'label'       => 'Superadmin (Global NOC)',
                'badge'       => 'bg-purple-100 text-purple-800 border-purple-200',
                'description' => 'Akses penuh ke semua site, manajemen user & edge router global',
            ],
            'site_admin' => [
                'label'       => 'Site Administrator',
                'badge'       => 'bg-blue-100 text-blue-800 border-blue-200',
                'description' => 'Akses penuh untuk mengelola 1 site spesifik (Studio, Profil, User, & Router)',
            ],
            'operator' => [
                'label'       => 'Site Operator',
                'badge'       => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'description' => 'Mengelola tamu, voucher, dan monitoring sesi untuk 1 site spesifik',
            ],
            'cashier' => [
                'label'       => 'Kasir / Frontdesk',
                'badge'       => 'bg-amber-100 text-amber-800 border-amber-200',
                'description' => 'Hanya pembuatan & cetak batch voucher hotspot untuk 1 site spesifik',
            ],
            'advertiser' => [
                'label'       => 'Advertiser / Sponsor',
                'badge'       => 'bg-slate-100 text-slate-800 border-slate-200',
                'description' => 'Akses kampanye survei & analitik audiens',
            ],
        ];

        return view('admin.users.index', compact('users', 'sites', 'roles'));
    }

    /**
     * Store a newly created operator in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|max:150|unique:users,email',
            'password' => 'required|string|min:8',
            'role'     => 'required|string|in:superadmin,site_admin,operator,cashier,advertiser',
            'site_id'  => [
                'nullable',
                Rule::requiredIf($request->input('role') !== 'superadmin'),
                'exists:locations,id',
            ],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;

        if ($validated['role'] === 'superadmin') {
            $validated['site_id'] = null;
        }

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Operator {$validated['name']} berhasil ditambahkan.");
    }

    /**
     * Update the specified operator in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
            'role'     => 'required|string|in:superadmin,site_admin,operator,cashier,advertiser',
            'site_id'  => [
                'nullable',
                Rule::requiredIf($request->input('role') !== 'superadmin'),
                'exists:locations,id',
            ],
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($validated['role'] === 'superadmin') {
            $validated['site_id'] = null;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun operator {$user->name} berhasil diperbarui.");
    }

    /**
     * Toggle operator active status.
     */
    public function toggle(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Akun operator {$user->name} berhasil {$status}.");
    }

    /**
     * Remove the specified operator from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun operator {$name} berhasil dihapus.");
    }
}
