<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortalMember;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = PortalMember::latest()->paginate(20);
        return view('admin.members.index', compact('members'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'username'     => 'required|string|max:50|unique:portal_members,username',
            'password'     => 'required|string|min:6|max:255',
            'full_name'    => 'required|string|max:255',
            'role'         => 'required|in:staff,vip',
            'rate_limit'   => 'nullable|string|max:20',
            'shared_users' => 'nullable|integer|min:1|max:10',
            'is_active'    => 'boolean',
        ]);

        PortalMember::create([
            'username'     => $request->username,
            'password'     => $request->password,
            'full_name'    => $request->full_name,
            'role'         => $request->role,
            'rate_limit'   => $request->rate_limit ?? '20M/20M',
            'shared_users' => $request->shared_users ?? 2,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.members.index')
            ->with('success', "Akun member \"{$request->username}\" berhasil dibuat.");
    }

    public function update(Request $request, PortalMember $member)
    {
        $request->validate([
            'username'     => "required|string|max:50|unique:portal_members,username,{$member->id}",
            'password'     => 'nullable|string|min:6|max:255',
            'full_name'    => 'required|string|max:255',
            'role'         => 'required|in:staff,vip',
            'rate_limit'   => 'nullable|string|max:20',
            'shared_users' => 'nullable|integer|min:1|max:10',
            'is_active'    => 'boolean',
        ]);

        $data = [
            'username'     => $request->username,
            'full_name'    => $request->full_name,
            'role'         => $request->role,
            'rate_limit'   => $request->rate_limit ?? '20M/20M',
            'shared_users' => $request->shared_users ?? 2,
            'is_active'    => $request->boolean('is_active', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $member->update($data);

        return redirect()->route('admin.members.index')
            ->with('success', "Member \"{$member->username}\" berhasil diperbarui.");
    }

    public function destroy(PortalMember $member)
    {
        $member->delete();
        return redirect()->route('admin.members.index')
            ->with('success', 'Member berhasil dihapus.');
    }

    public function toggle(PortalMember $member)
    {
        $member->update(['is_active' => !$member->is_active]);
        $status = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Member \"{$member->username}\" berhasil $status.");
    }
}
