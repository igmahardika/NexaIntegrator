@extends('layouts.admin')
@section('title', 'Member & Staff Accounts')
@section('page-title', 'Member & Staff Accounts')
@section('page-subtitle', 'Manage bypass accounts, QoS bandwidth tiers, and concurrent devices for staff and VIP guests')

@section('content')
<div x-data="memberManager()" class="space-y-6">

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-200 flex items-center justify-center text-[#22449E] shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Accounts</div>
                <div class="text-xl font-black text-slate-900">{{ $members->total() }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Active Accounts</div>
                <div class="text-xl font-black text-emerald-600">
                    {{ $members->filter(fn($m) => $m->is_active)->count() }}
                </div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-11 h-11 rounded-xl bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">VIP Guests</div>
                <div class="text-xl font-black text-purple-600">
                    {{ $members->filter(fn($m) => $m->role === 'vip')->count() }}
                </div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-3.5 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-11 h-11 rounded-xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-cyan-600 shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Internal Staff</div>
                <div class="text-xl font-black text-cyan-600">
                    {{ $members->filter(fn($m) => $m->role === 'staff')->count() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Add Member Form Panel -->
        <div class="card p-6 h-fit bg-white border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-[#22449E] flex items-center justify-center font-bold">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Create Member Account</h3>
                </div>
            </div>
            <p class="text-xs text-slate-500 mb-5">
                Member credentials bypass ad campaigns and surveys, authenticating directly against Edge Router radius/local DB.
            </p>

            <form method="POST" action="{{ route('admin.members.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="label text-slate-700 font-semibold">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" class="input" required value="{{ old('full_name') }}" placeholder="e.g. John Doe">
                    @error('full_name')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" class="input font-mono" required value="{{ old('username') }}" placeholder="john.doe">
                    @error('username')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" class="input font-mono" required minlength="6" placeholder="Min. 6 characters">
                    @error('password')<p class="text-rose-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">Account Role <span class="text-rose-500">*</span></label>
                        <select name="role" class="input bg-white" required>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                            <option value="vip" {{ old('role') === 'vip' ? 'selected' : '' }}>VIP Guest</option>
                        </select>
                    </div>

                    <div>
                        <label class="label text-slate-700 font-semibold">Concurrent Devices</label>
                        <input type="number" name="shared_users" class="input" min="1" max="10" value="{{ old('shared_users', 2) }}">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Bandwidth Rate-Limit</label>
                    <input type="text" name="rate_limit" class="input font-mono" value="{{ old('rate_limit', '20M/20M') }}" placeholder="20M/20M">
                    <p class="text-[11px] text-slate-400 mt-1">Format: [Upload]/[Download]</p>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" id="create_active" checked class="w-4 h-4 rounded border-slate-300 text-[#22449E] focus:ring-[#22449E]">
                    <label for="create_active" class="text-xs text-slate-700 select-none cursor-pointer font-medium">Activate account immediately</label>
                </div>

                <button type="submit" class="btn-primary w-full justify-center flex items-center gap-2 py-2.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Save Account</span>
                </button>
            </form>
        </div>

        <!-- Member Directory Table -->
        <div class="lg:col-span-2 space-y-4">
            <div class="card overflow-hidden bg-white border border-slate-200/80 shadow-xs">
                <div class="p-4 border-b border-slate-200/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white">
                    <div>
                        <h3 class="font-bold text-slate-900 flex items-center gap-2 text-sm uppercase tracking-wider">
                            <span>Directory of Members & Staff</span>
                        </h3>
                        <div class="text-xs text-slate-500">
                            Total registered user accounts: <strong>{{ $members->total() }}</strong>
                        </div>
                    </div>
                </div>

                @if($members->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="font-semibold text-slate-800 mb-1">No Member Accounts Found</div>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto">
                        Use the panel on the left to add your first staff or VIP member account.
                    </p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200/80 bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                                <th class="py-3 px-4 font-semibold">User & Name</th>
                                <th class="py-3 px-4 font-semibold">Role</th>
                                <th class="py-3 px-4 font-semibold">Rate Limit</th>
                                <th class="py-3 px-4 font-semibold">Concurrency</th>
                                <th class="py-3 px-4 font-semibold">Status</th>
                                <th class="py-3 px-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($members as $m)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $m->full_name }}</div>
                                    <div class="font-mono text-slate-500 text-[11px]">{{ $m->username }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    @if($m->role === 'vip')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        VIP
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-[#22449E] border border-blue-200">
                                        Staff
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600 font-medium">
                                    {{ $m->rate_limit ?? 'Default' }}
                                </td>
                                <td class="py-3 px-4 text-slate-700 font-medium">
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-800">
                                        {{ $m->shared_users }} devices
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($m->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Suspended</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Toggle Status -->
                                        <form method="POST" action="{{ route('admin.members.toggle', $m) }}" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-colors {{ $m->is_active ? 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' }}"
                                                title="{{ $m->is_active ? 'Suspend Account' : 'Activate Account' }}"
                                            >
                                                {{ $m->is_active ? 'Suspend' : 'Activate' }}
                                            </button>
                                        </form>

                                        <!-- Edit Modal Trigger -->
                                        <button
                                            type="button"
                                            @click="openEditModal(@js($m))"
                                            class="btn-secondary px-2.5 py-1 text-xs font-semibold"
                                            title="Edit Member"
                                        >
                                            Edit
                                        </button>

                                        <!-- Delete -->
                                        <form method="POST" action="{{ route('admin.members.destroy', $m) }}" onsubmit="return confirm('Permanently remove user {{ $m->username }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger px-2.5 py-1 text-xs" title="Delete Member">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($members->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $members->links() }}
                </div>
                @endif
                @endif
            </div>
        </div>

    </div>

    <!-- Edit Modal -->
    <div
        x-show="editModalOpen"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="editModalOpen = false"
    >
        <div class="card max-w-md w-full p-6 bg-white border border-slate-200 shadow-2xl relative" @click.outside="editModalOpen = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-base">Edit Member Account</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>

            <form :action="editFormAction" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="label text-slate-700 font-semibold">Full Name <span class="text-rose-500">*</span></label>
                    <input type="text" name="full_name" x-model="editingMember.full_name" class="input" required>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Username <span class="text-rose-500">*</span></label>
                    <input type="text" name="username" x-model="editingMember.username" class="input font-mono" required>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">New Password <span class="text-slate-400 font-normal">(leave blank to keep current)</span></label>
                    <input type="password" name="password" class="input font-mono" minlength="6" placeholder="Leave empty to retain existing password">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-slate-700 font-semibold">Account Role <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="editingMember.role" class="input bg-white" required>
                            <option value="staff">Staff</option>
                            <option value="vip">VIP Guest</option>
                        </select>
                    </div>

                    <div>
                        <label class="label text-slate-700 font-semibold">Concurrency</label>
                        <input type="number" name="shared_users" x-model="editingMember.shared_users" class="input" min="1" max="10">
                    </div>
                </div>

                <div>
                    <label class="label text-slate-700 font-semibold">Bandwidth Rate-Limit</label>
                    <input type="text" name="rate_limit" x-model="editingMember.rate_limit" class="input font-mono" placeholder="20M/20M">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" name="is_active" value="1" id="edit_active" :checked="editingMember.is_active" class="w-4 h-4 rounded border-slate-300 text-[#22449E]">
                    <label for="edit_active" class="text-xs text-slate-700 select-none cursor-pointer font-medium">Account is active</label>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="btn-secondary text-xs px-4 py-2 font-semibold">Cancel</button>
                    <button type="submit" class="btn-primary text-xs px-4 py-2 font-semibold">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
function memberManager() {
    return {
        editModalOpen: false,
        editingMember: {},
        editFormAction: '',

        openEditModal(member) {
            this.editingMember = Object.assign({}, member);
            this.editFormAction = `{{ url('admin/members') }}/${member.id}`;
            this.editModalOpen = true;
        }
    }
}
</script>
@endsection
