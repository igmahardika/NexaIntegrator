<div 
    x-data="globalConfirmDialog()"
    @open-confirm.window="open($event.detail)"
    x-cloak
>
    <div 
        x-show="isOpen" 
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs overflow-y-auto"
        @keydown.escape.window="close()"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            @click.outside="close()"
            class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden my-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        >
            <div class="p-6">
                <div class="flex items-start gap-4">
                    <div 
                        :class="isDestructive ? 'bg-rose-50 text-rose-600 border-rose-200' : 'bg-blue-50 text-brand border-blue-200'"
                        class="w-11 h-11 rounded-xl border flex items-center justify-center shrink-0 shadow-2xs"
                    >
                        <template x-if="isDestructive">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </template>
                        <template x-if="!isDestructive">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                    </div>

                    <div class="min-w-0 flex-1">
                        <h3 id="confirm-modal-title" class="text-base font-extrabold text-slate-900 leading-snug" x-text="title"></h3>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed" x-text="message"></p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button 
                    type="button" 
                    @click="close()" 
                    class="btn-secondary text-xs font-semibold py-2 px-3.5"
                    x-text="cancelLabel"
                ></button>

                <template x-if="actionUrl">
                    <form :action="actionUrl" :method="method.toUpperCase() === 'GET' ? 'GET' : 'POST'">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <template x-if="['PUT', 'PATCH', 'DELETE'].includes(method.toUpperCase())">
                            <input type="hidden" name="_method" :value="method.toUpperCase()">
                        </template>
                        <button 
                            type="submit" 
                            :class="isDestructive ? 'btn-danger' : 'btn-primary'"
                            class="text-xs font-bold py-2 px-4 shadow-sm"
                            x-text="confirmLabel"
                        ></button>
                    </form>
                </template>

                <template x-if="!actionUrl && onConfirmCallback">
                    <button 
                        type="button" 
                        @click="executeCallback()" 
                        :class="isDestructive ? 'btn-danger' : 'btn-primary'"
                        class="text-xs font-bold py-2 px-4 shadow-sm"
                        x-text="confirmLabel"
                    ></button>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function globalConfirmDialog() {
    return {
        isOpen: false,
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmLabel: 'Ya, Lanjutkan',
        cancelLabel: 'Batal',
        actionUrl: '',
        method: 'POST',
        isDestructive: true,
        onConfirmCallback: null,

        open(detail = {}) {
            this.title = detail.title || (detail.isDestructive !== false ? 'Hapus Data Permanen' : 'Konfirmasi Aksi');
            this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan? Tindakan ini tidak dapat dibatalkan.';
            this.confirmLabel = detail.confirmLabel || (detail.isDestructive !== false ? 'Hapus Permanen' : 'Ya, Lanjutkan');
            this.cancelLabel = detail.cancelLabel || 'Batal';
            this.actionUrl = detail.actionUrl || detail.action || '';
            this.method = (detail.method || 'POST').toUpperCase();
            this.isDestructive = detail.isDestructive !== undefined ? Boolean(detail.isDestructive) : true;
            this.onConfirmCallback = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.isOpen = true;
        },

        close() {
            this.isOpen = false;
        },

        executeCallback() {
            if (this.onConfirmCallback) {
                this.onConfirmCallback();
            }
            this.close();
        }
    };
}
</script>
