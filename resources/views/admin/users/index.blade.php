@extends('layouts.admin')
@section('title', 'Operator & Akses User Dashboard')
@section('page-title', 'Operator & Akses User')
@section('page-subtitle', 'Manajemen hak akses dashboard, penugasan site operator, dan pemisahan wewenang NOC vs Cabang')

@section('content')
<div x-data="userAccessManager()" class="space-y-6">

    <!-- KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Total Operator</div>
                <div class="text-2xl font-black text-slate-900">{{ $users->total() }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Akun Aktif</div>
                <div class="text-2xl font-black text-emerald-600">{{ $users->where('is_active', true)->count() }}</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-brand shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Site Terdaftar</div>
                <div class="text-2xl font-black text-brand">{{ $sites->count() }} Site</div>
            </div>
        </div>

        <div class="card p-4 flex items-center gap-4 bg-white border border-slate-200/80 shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <div>
                <div class="text-2xs font-bold text-slate-500 uppercase tracking-wider">Level Hak Akses</div>
                <div class="text-2xl font-black text-amber-600">5 Role</div>
            </div>
        </div>
    </div>

    <!-- Filter & Action Bar -->
    <div class="card p-4 flex flex-col sm:flex-row gap-3 items-center justify-between bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-2 items-center w-full sm:w-auto">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari nama atau email..."
                class="input input-sm w-full sm:w-56"
            >
            <select name="role" class="input input-sm w-full sm:w-44" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                @foreach($roles as $key => $r)
                <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $r['label'] }}</option>
                @endforeach
            </select>
            <select name="site_id" class="input input-sm w-full sm:w-48" onchange="this.form.submit()">
                <option value="">Semua Penugasan Site</option>
                @foreach($sites as $s)
                <option value="{{ $s->id }}" {{ request('site_id') === $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary text-xs font-semibold">Filter</button>
            @if(request()->hasAny(['search', 'role', 'site_id']))
            <a href="{{ route('admin.users.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline py-2 font-medium">Reset</a>
            @endif
        </form>

        <button @click="openModal('create')" class="btn-primary flex items-center gap-2 w-full sm:w-auto justify-center shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Operator Baru</span>
        </button>
    </div>

    <!-- Operator Accounts Table -->
    <div class="card bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200">
                        <th class="table-th">Nama Operator</th>
                        <th class="table-th">Role / Wewenang</th>
                        <th class="table-th">Penugasan Site</th>
                        <th class="table-th">Status</th>
                        <th class="table-th">Terdaftar</th>
                        <th class="table-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand/10 text-brand font-bold flex items-center justify-center text-xs shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                        <span class="px-1.5 py-0.5 bg-brand/10 text-brand text-2xs font-extrabold rounded-md">Anda</span>
                                        @endif
                                    </div>
                                    <div class="text-slate-500 font-mono text-xs">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold border {{ $roles[$user->role]['badge'] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $roles[$user->role]['label'] ?? $user->role }}
                            </span>
                            <div class="text-2xs text-slate-500 mt-0.5 max-w-xs truncate font-medium">
                                {{ $roles[$user->role]['description'] ?? '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
                            @if($user->isSuperadmin())
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-2xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                <span>🌐</span> Global NOC (Semua Site)
                            </span>
                            @elseif($user->site)
                            <div class="flex items-center gap-1.5 font-semibold text-slate-800">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>{{ $user->site->name }}</span>
                            </div>
                            <div class="text-2xs text-slate-500 font-mono">{{ $user->site->slug }}</div>
                            @else
                            <span class="text-rose-500 font-medium italic">Belum diasosiasikan</span>
                            @endif
                        </td>
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-500 font-mono text-2xs">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <button 
                                    @click="openModal('edit', {{ json_encode([
                                        'id' => $user->id,
                                        'name' => $user->name,
                                        'email' => $user->email,
                                        'role' => $user->role,
                                        'site_id' => $user->site_id,
                                    ]) }})"
                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                >
                                    Edit
                                </button>

                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg {{ $user->is_active ? 'bg-amber-50 hover:bg-amber-100 text-amber-800' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800' }} transition">
                                        {{ $user->is_active ? 'Suspend' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus operator {{ $user->name }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 transition">
                                        Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400">
                            Tidak ada akun operator yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- ============================================================= -->
    <!-- MODAL CREATE / EDIT OPERATOR -->
    <!-- ============================================================= -->
    <div 
        x-show="modalOpen" 
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-labelledby="operator-modal-title"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @keydown.escape.window="modalOpen = false"
    >
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden" @click.away="modalOpen = false">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h3 id="operator-modal-title" class="text-sm font-bold text-slate-900" x-text="modalMode === 'create' ? 'Tambah Operator Dashboard' : 'Edit Akun Operator'"></h3>
                <button type="button" @click="modalOpen = false" aria-label="Tutup dialog" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors text-lg leading-none">&times;</button>
            </div>

            <form :action="modalMode === 'create' ? '{{ route('admin.users.store') }}' : '/admin/users/' + formData.id" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <template x-if="modalMode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" x-model="formData.name" required class="input input-sm w-full" placeholder="cth. Budi Santoso">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" x-model="formData.email" required class="input input-sm w-full" placeholder="operator@cabang.com">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Role / Hak Akses <span class="text-rose-500">*</span></label>
                        <select name="role" x-model="formData.role" required class="input input-sm w-full">
                            @foreach($roles as $key => $r)
                            <option value="{{ $key }}">{{ $r['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="formData.role !== 'superadmin'">
                        <label class="block font-semibold text-slate-700 mb-1">Penugasan Site <span class="text-rose-500">*</span></label>
                        <select name="site_id" x-model="formData.site_id" :required="formData.role !== 'superadmin'" class="input input-sm w-full">
                            <option value="">-- Pilih Site Cabang --</option>
                            @foreach($sites as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        <span x-text="modalMode === 'create' ? 'Password' : 'Password Baru (Kosongkan jika tidak diubah)'"></span>
                        <span x-show="modalMode === 'create'" class="text-rose-500">*</span>
                    </label>
                    <input type="password" name="password" :required="modalMode === 'create'" class="input input-sm w-full" placeholder="Minimal 8 karakter">
                </div>

                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-2xs text-blue-900 leading-relaxed">
                    <strong>🛡️ Kebijakan Isolasi Data:</strong> Operator yang ditugaskan ke Site Cabang secara otomatis dikunci hanya untuk melihat data tamu, voucher, dan router pada site tersebut.
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" @click="modalOpen = false" class="btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs shadow-sm">
                        <span x-text="modalMode === 'create' ? 'Simpan Operator' : 'Perbarui Akun'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
function userAccessManager() {
    return {
        modalOpen: false,
        modalMode: 'create',
        formData: {
            id: null,
            name: '',
            email: '',
            role: 'operator',
            site_id: '{{ $sites->first()?->id }}',
        },
        openModal(mode, data = null) {
            this.modalMode = mode;
            if (mode === 'edit' && data) {
                this.formData = Object.assign({}, data);
            } else {
                this.formData = {
                    id: null,
                    name: '',
                    email: '',
                    role: 'operator',
                    site_id: '{{ $sites->first()?->id }}',
                };
            }
            this.modalOpen = true;
        }
    };
}
</script>
@endpush
@endsection
