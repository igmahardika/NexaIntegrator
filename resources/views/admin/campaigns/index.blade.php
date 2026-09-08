@extends('layouts.admin')
@section('title', 'Campaigns & Engagement')
@section('page-title', 'Campaigns & Engagement')
@section('page-subtitle', 'Manage sponsor advertisements, multimedia video promos, and questionnaire surveys')

@section('header-actions')
<a href="{{ route('admin.campaigns.create') }}" class="btn-primary text-xs py-2 px-4 flex items-center gap-1.5 shadow-sm font-semibold">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
    </svg>
    <span>New Campaign</span>
</a>
@endsection

@section('content')
<div class="card overflow-hidden bg-white border border-slate-200/80 shadow-xs">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead class="border-b border-slate-200/80 bg-slate-50 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                <tr>
                    <th class="text-left py-3.5 px-4">Campaign & Media</th>
                    <th class="text-left py-3.5 px-4 hidden md:table-cell">Sponsor Brand</th>
                    <th class="text-left py-3.5 px-4 hidden lg:table-cell">Flight Schedule</th>
                    <th class="text-left py-3.5 px-4 hidden lg:table-cell">Survey Items</th>
                    <th class="text-left py-3.5 px-4">Responses</th>
                    <th class="text-left py-3.5 px-4">Status</th>
                    <th class="py-3.5 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse($campaigns as $campaign)
                <tr class="hover:bg-slate-50/70 transition-colors">
                    <td class="py-3 px-4">
                        <div class="font-bold text-slate-900 text-sm">{{ Str::limit($campaign->title, 40) }}</div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-1">
                            @if($campaign->hasVideo())
                                <svg class="w-3.5 h-3.5 text-[#22449E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.069A1 1 0 0121 8.82V15.18a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span>Video Ad ({{ $campaign->min_watch_duration }}s)</span>
                            @else
                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Display Banner</span>
                            @endif
                        </div>
                    </td>
                    <td class="py-3 px-4 hidden md:table-cell text-slate-700 text-xs font-medium">{{ $campaign->sponsor_name ?? '—' }}</td>
                    <td class="py-3 px-4 hidden lg:table-cell text-xs text-slate-500 font-mono">
                        {{ $campaign->start_date?->format('d M Y') ?? 'Immediate' }}
                        — {{ $campaign->end_date?->format('d M Y') ?? 'Permanent' }}
                    </td>
                    <td class="py-3 px-4 hidden lg:table-cell">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            {{ $campaign->questions_count }} questions
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <span class="font-black text-slate-900 font-mono text-sm">{{ number_format($campaign->responses_count) }}</span>
                    </td>
                    <td class="py-3 px-4">
                        @if($campaign->is_active && !$campaign->isExpired())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                        @elseif($campaign->isExpired())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">Expired</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Draft</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex items-center gap-2 justify-end">
                            <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="btn-secondary text-xs py-1.5 px-3 font-semibold">Edit</a>
                            <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign permanently?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-danger text-xs py-1.5 px-2.5">✕</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center text-slate-400">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                        </div>
                        <p class="font-bold text-slate-800 mb-1 text-sm">No Active Campaigns Found</p>
                        <p class="text-xs text-slate-500">Create your first sponsor ad or questionnaire survey.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($campaigns->hasPages())
    <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
        {{ $campaigns->links() }}
    </div>
    @endif
</div>
@endsection
