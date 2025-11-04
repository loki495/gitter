@persist('toasts')
    <div
        x-data="toastHandler()"
        x-on:notify.window="add($event.detail)"
        class="fixed inset-0 flex flex-col items-end justify-start px-4 py-6 space-y-3 pointer-events-none sm:p-6 z-50 text-white"
    >
        <template x-for="(toast, index) in toasts" :key="index">
            <div
                x-show="visible.includes(index)"
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="max-w-sm w-full bg-zinc-800 text-white shadow-xlg rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border-2 mt-2"
                :class="toast?.type === 'info' ? 'border-blue-400' : toast?.type === 'success' ? 'border-green-400' : toast?.type === 'error' ? 'border-red-400' : 'border-yellow-400'"
            >
                <div class="p-4 flex items-start">
                    <div class="flex-shrink-0" :class="iconColor(toast.type)">
                        <template x-if="toast.type === 'success'">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                    d="M5 13l4 4L19 7" /></svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                    d="M6 18L18 6M6 6l12 12" /></svg>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856C18.07 19 19 17.97 19 16.8V7.2C19 6.03 18.07 5 16.918 5H7.082C5.93 5 5 6.03 5 7.2v9.6C5 17.97 5.93 19 7.082 19z" /></svg>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" /></svg>
                        </template>
                    </div>
                    <div class="ml-3 min-w-0 flex-1">
                        <p class="text-sm font-medium break-words whitespace-pre-wrap" x-text="toast.message"></p>
                    </div>
                    <button
                        @click="remove(index)"
                        class="ml-4 inline-flex text-zinc-400 hover:text-zinc-200 focus:outline-none cursor-pointer"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round"
                                d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

@php
$notify = session('notify');
@endphp

<script>
    document.addEventListener('alpine:init', () => {
        @if ($notify)
        alert({{ $notify['message'] }});
        @endif
        Alpine.data('toastHandler', () => ({
            toasts: [],
            visible: [],
            add(toast) {
                const index = this.toasts.push(toast[0]) - 1;
                this.$nextTick(() => {
                    this.visible.push(index);
                    setTimeout(() => this.remove(index), 4000);
                    if (toast[0].redirect) {
                        Livewire.navigate(toast[0].redirect);
                    }
                });
            },
            remove(index) {
                this.visible = this.visible.filter(i => i !== index);
                setTimeout(() => this.toasts.splice(index, 1), 300);
            },
            iconColor(type) {
                return {
                    success: 'text-green-400',
                    error: 'text-red-400',
                    warning: 'text-yellow-400',
                    info: 'text-blue-400'
                }[type] || 'text-white';
            },
        }));


        document.addEventListener('livewire:init', () => {
            @if ($notify)
                $wire.dispatch('notify', {
                    type: @js($notify['type']),
                    message: @js($notify['message']),
                });
            @endif
        });
    });
</script>

@endpersist
