@props([
    'title' => 'Lanjutkan tindakan?',
    'message' => 'Pastikan data sudah benar sebelum melanjutkan.',
    'confirmLabel' => 'Lanjutkan',
    'method' => 'POST',
    'variant' => 'danger',
])

@php
    $tone = $variant === 'danger'
        ? 'bg-rose-600 shadow-rose-600/20 hover:bg-rose-500 focus:ring-rose-500'
        : 'bg-blue-600 shadow-blue-600/20 hover:bg-blue-500 focus:ring-blue-500';

    $iconTone = $variant === 'danger'
        ? 'bg-rose-100 text-rose-600'
        : 'bg-blue-100 text-blue-600';
@endphp

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
    x-on:wms-confirm.window="openDialog($event.detail)"
    x-effect="document.body.classList.toggle('overflow-y-hidden', open)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
    role="dialog"
    aria-modal="true"
    @keydown.escape.window="open = false"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>

    <div
        x-show="open"
        x-transition
        class="relative w-full max-w-md overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl shadow-blue-950/20"
    >
        <div class="p-6 sm:p-7">
            <div class="flex items-start gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $iconTone }}">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <h2 class="text-base font-semibold text-slate-900">{{ $title }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">
                        {{ $message }}
                        <span class="font-semibold text-slate-900" x-text="name"></span>
                    </p>
                </div>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 border-t border-blue-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
            <button type="button" x-ref="cancel" @click="open = false" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Batal
            </button>

            <form method="POST" :action="action" @submit="open = false">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 sm:w-auto {{ $tone }}">
                    {{ $confirmLabel }}
                </button>
            </form>
        </div>
    </div>
</div>
