@php
    $popup = session('popup');

    if (blank($popup) && filled(session('success'))) {
        $popup = ['type' => 'success', 'title' => 'Berhasil', 'message' => session('success')];
    } elseif (blank($popup) && filled(session('warning'))) {
        $popup = ['type' => 'warning', 'title' => 'Perlu diperhatikan', 'message' => session('warning')];
    } elseif (blank($popup) && filled(session('error'))) {
        $popup = ['type' => 'error', 'title' => 'Tidak dapat diproses', 'message' => session('error')];
    }

    if (blank($popup) && isset($errors) && $errors->has('google')) {
        $popup = [
            'type' => 'error',
            'title' => 'Login Google gagal',
            'message' => $errors->first('google'),
        ];
    }

    $type = $popup['type'] ?? 'info';

    $theme = match ($type) {
        'success' => [
            'bar' => 'from-emerald-500 to-teal-400',
            'icon' => 'bg-emerald-100 text-emerald-600',
        ],
        'warning' => [
            'bar' => 'from-amber-500 to-orange-400',
            'icon' => 'bg-amber-100 text-amber-600',
        ],
        'error' => [
            'bar' => 'from-red-500 to-rose-400',
            'icon' => 'bg-red-100 text-red-600',
        ],
        default => [
            'bar' => 'from-blue-500 to-sky-400',
            'icon' => 'bg-blue-100 text-blue-600',
        ],
    };
@endphp

@if (filled($popup))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 8000)"
        x-show="show"
        x-cloak
        @keydown.escape.window="show = false"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="fixed right-4 top-20 z-40 w-80 max-w-[calc(100vw_-_2rem)] sm:w-96 lg:top-4"
        role="{{ $type === 'error' ? 'alert' : 'status' }}"
        aria-live="{{ $type === 'error' ? 'assertive' : 'polite' }}"
    >
        <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-4 shadow-xl shadow-blue-900/10">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r {{ $theme['bar'] }}" aria-hidden="true"></div>

            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $theme['icon'] }}">
                    @if ($type === 'success')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    @elseif ($type === 'error' || $type === 'warning')
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM12 3.75 1.5 21h21L12 3.75Z" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    @endif
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-semibold text-slate-900">{{ $popup['title'] ?? 'Pemberitahuan' }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $popup['message'] ?? '' }}</p>
                </div>

                <button
                    type="button"
                    @click="show = false"
                    class="-m-1 shrink-0 rounded-full p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    aria-label="Tutup notifikasi"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
