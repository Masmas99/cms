@props([
    'title' => 'Hapus data?',
    'message' => 'Data akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.',
    'confirmLabel' => 'Hapus',
])

<div
    x-data="{
        open: false,
        action: '',
        name: '',
        openDialog(detail) {
            this.action = detail.action;
            this.name = detail.name ?? '';
            this.open = true;
            this.$nextTick(() => this.$refs.cancel?.focus());
        },
    }"
    x-on:wms-delete.window="openDialog($event.detail)"
    x-effect="document.body.classList.toggle('overflow-y-hidden', open)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    :aria-labelledby="$id('delete-title')"
    :aria-describedby="$id('delete-description')"
    @keydown.escape.window="open = false"
>
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
        @click="open = false"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl shadow-blue-950/20"
    >
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <h2 :id="$id('delete-title')" class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                    <p :id="$id('delete-description')" class="mt-2 text-sm leading-relaxed text-slate-500">
                        {{ $message }}
                        <span class="font-semibold text-slate-900" x-text="name"></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-blue-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
            <button
                type="button"
                x-ref="cancel"
                @click="open = false"
                class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Batal
            </button>

            <form method="POST" :action="action" @submit="open = false">
                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-rose-600/20 transition hover:bg-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 sm:w-auto"
                >
                    {{ $confirmLabel }}
                </button>
            </form>
        </div>
    </div>
</div>
