@extends('layouts.admin')
@section('title', 'Voucher Management')
@section('page-title', 'Voucher Management')
@section('page-subtitle', 'Generate and manage client access codes with bandwidth rate-limits and expiration')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Generator Panel -->
    <div class="card p-6 bg-white border border-slate-200/80 shadow-xs h-fit">
        <div class="flex items-center gap-2 mb-4 pb-3 border-b border-slate-100">
            <div class="w-8 h-8 rounded-lg bg-blue-50 text-brand flex items-center justify-center font-bold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Generate Batch Vouchers</h3>
        </div>

        <form method="POST" action="{{ route('admin.vouchers.generate') }}" class="space-y-4">
            @csrf

            <div>
                <label class="label text-slate-700 font-semibold">Batch Name / Identifier <span class="text-rose-500">*</span></label>
                <input type="text" name="batch_name" class="input font-mono" required value="{{ old('batch_name') }}"
                    placeholder="BATCH-PROMO-001" style="text-transform:uppercase">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Voucher Quantity <span class="text-rose-500">*</span></label>
                <input type="number" name="count" class="input" required value="{{ old('count', 10) }}" min="1" max="500">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Duration (Minutes) <span class="text-rose-500">*</span></label>
                <input type="number" name="duration_minutes" class="input" required value="{{ old('duration_minutes', 60) }}" min="1">
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Bandwidth Speed (Rate Limit)</label>
                <select name="rate_limit" class="input">
                    <option value="2M/2M">2 Mbps / 2 Mbps</option>
                    <option value="5M/5M" selected>5 Mbps / 5 Mbps</option>
                    <option value="10M/10M">10 Mbps / 10 Mbps</option>
                    <option value="20M/20M">20 Mbps / 20 Mbps</option>
                    <option value="50M/50M">50 Mbps / 50 Mbps</option>
                </select>
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Assigned Site (Optional)</label>
                <select name="location_id" class="input">
                    <option value="">— All Sites & Locations —</option>
                    @foreach($locations as $loc)
                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="label text-slate-700 font-semibold">Expiration Timestamp</label>
                <input type="datetime-local" name="expired_at" class="input" value="{{ old('expired_at') }}">
                <p class="text-[11px] text-slate-500 mt-1">Leave empty for perpetual vouchers with no expiry date.</p>
            </div>

            <button type="submit" class="btn-primary w-full shadow-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span>Generate Vouchers</span>
            </button>
        </form>
    </div>

    <!-- Voucher List -->
    <div class="lg:col-span-2 space-y-4">

        <!-- Filter Bar -->
        <div class="card p-3 bg-white border border-slate-200/80 shadow-xs">
            <form method="GET" class="flex flex-wrap gap-2 items-center justify-between">
                <div class="flex flex-wrap gap-2 items-center">
                    <select name="batch" class="input w-auto text-xs py-1.5 px-3">
                        <option value="">All Batches</option>
                        @foreach($batches as $b)
                        <option value="{{ $b }}" {{ request('batch') == $b ? 'selected' : '' }}>{{ $b }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="input w-auto text-xs py-1.5 px-3">
                        <option value="">All Statuses</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Active (Available)</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Claimed (Used)</option>
                    </select>

                    <button type="submit" class="btn-secondary text-xs py-1.5 px-3 font-semibold">Filter</button>
                    @if(request()->hasAny(['batch', 'status']))
                    <a href="{{ route('admin.vouchers.index') }}" class="text-xs text-slate-500 hover:text-slate-900 underline py-1">Reset</a>
                    @endif
                </div>

                @if(request('batch'))
                <a href="{{ route('admin.vouchers.print', request('batch')) }}" target="_blank" class="btn-primary text-xs py-1.5 px-3 flex items-center gap-1.5 font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print Batch</span>
                </a>
                @endif
            </form>
        </div>

        <div class="card overflow-hidden bg-white border border-slate-200/80 shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead class="border-b border-slate-200/80 bg-slate-50">
                        <tr>
                            <th class="table-th text-left">Voucher Code</th>
                            <th class="table-th text-left">Batch Name</th>
                            <th class="table-th text-left hidden md:table-cell">Duration</th>
                            <th class="table-th text-left hidden lg:table-cell">Speed Profile</th>
                            <th class="table-th text-left hidden lg:table-cell">Expires On</th>
                            <th class="table-th text-left">Status</th>
                            <th class="table-th text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($vouchers as $v)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-2.5 px-4 font-mono font-bold text-slate-900 tracking-wider text-xs">
                                <span class="bg-blue-50 text-brand px-2 py-0.5 rounded border border-blue-200/60 font-semibold">{{ $v->code }}</span>
                            </td>
                            <td class="py-2.5 px-4 text-xs text-slate-600 font-medium">{{ $v->batch_name }}</td>
                            <td class="py-2.5 px-4 text-xs text-slate-600 hidden md:table-cell font-mono">{{ $v->duration_minutes }} min</td>
                            <td class="py-2.5 px-4 text-xs text-slate-600 hidden lg:table-cell font-mono">{{ $v->rate_limit }}</td>
                            <td class="py-2.5 px-4 text-xs text-slate-500 hidden lg:table-cell font-mono">
                                {{ $v->expired_at ? $v->expired_at->format('d/m/Y H:i') : 'Permanent' }}
                            </td>
                            <td class="py-2.5 px-4">
                                @if($v->is_used)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">Claimed</span>
                                @elseif($v->isExpired())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">Expired</span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-2xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                @endif
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                <form method="POST" action="{{ route('admin.vouchers.destroy', $v) }}" onsubmit="return confirm('Delete this voucher permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" aria-label="Delete voucher {{ $v->code }}" class="btn-danger text-xs py-1 px-2.5" title="Delete Voucher">✕</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                    </svg>
                                </div>
                                <p class="text-xs font-medium text-slate-500">No vouchers found matching your query.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($vouchers->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">{{ $vouchers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
