@props([
    'name' => 'modalOpen',
    'title' => '',
    'subtitle' => null,
    'maxWidth' => 'max-w-md',
    'id' => null
])

@php
$titleId = $id ?? 'modal-title-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', $name);
@endphp

<div 
    x-show="{{ $name }}" 
    x-cloak
    role="dialog"
    aria-modal="true"
    aria-labelledby="{{ $titleId }}"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    @keydown.escape.window="{{ $name }} = false"
>
    <div 
        @click.outside="{{ $name }} = false" 
        class="bg-white rounded-2xl {{ $maxWidth }} w-full shadow-2xl border border-slate-200 overflow-hidden"
    >
        @if($title || isset($header))
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            @if(isset($header))
                {{ $header }}
            @else
                <div>
                    <h3 id="{{ $titleId }}" class="text-sm font-bold text-slate-900">{{ $title }}</h3>
                    @if($subtitle)
                    <p class="text-2xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
            <button 
                type="button" 
                @click="{{ $name }} = false" 
                aria-label="Tutup dialog" 
                class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors shrink-0"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        <div class="p-6">
            {{ $slot }}
        </div>

        @if(isset($footer))
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
