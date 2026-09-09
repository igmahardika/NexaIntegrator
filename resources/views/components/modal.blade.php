@props([
    'name' => 'modalOpen',
    'title' => '',
    'subtitle' => null,
    'xTitle' => null,
    'maxWidth' => 'max-w-md',
    'id' => null,
    'maxHeight' => 'max-h-[90vh]'
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
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
    @keydown.escape.window="{{ $name }} = false"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <div 
        @click.outside="{{ $name }} = false" 
        class="bg-white rounded-2xl {{ $maxWidth }} w-full shadow-2xl border border-slate-200 overflow-hidden {{ $maxHeight }} flex flex-col my-auto"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
    >
        @if($title || $xTitle || isset($header))
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
            @if(isset($header))
                {{ $header }}
            @else
                <div class="min-w-0 pr-2">
                    @if($xTitle)
                        <h3 id="{{ $titleId }}" class="text-sm font-bold text-slate-900 truncate" x-text="{{ $xTitle }}"></h3>
                    @else
                        <h3 id="{{ $titleId }}" class="text-sm font-bold text-slate-900 truncate">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="text-2xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
            <button 
                type="button" 
                @click="{{ $name }} = false" 
                aria-label="Tutup dialog" 
                class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 focus-visible:ring-2 focus-visible:ring-brand focus-visible:outline-none transition-colors shrink-0 cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        @endif

        <div class="p-6 overflow-y-auto flex-1">
            {{ $slot }}
        </div>

        @if(isset($footer))
        <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
