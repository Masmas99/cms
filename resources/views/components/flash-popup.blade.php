@php
    $popup = session('popup');

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
            'icon' => 'bg-emerald-100 text-emerald-600',
            'button' => 'bg-emerald-600 hover:bg-emerald-500 focus:ring-emerald-500',
        ],
        'warning' => [
            'icon' => 'bg-amber-100 text-amber-600',
            'button' => 'bg-amber-600 hover:bg-amber-500 focus:ring-amber-500',
        ],
        'error' => [
            'icon' => 'bg-red-100 text-red-600',
            'button' => 'bg-red-600 hover:bg-red-500 focus:ring-red-500',
        ],
        default => [
            'icon' => 'bg-indigo-100 text-indigo-600',
            'button' => 'bg-indigo-600 hover:bg-indigo-500 focus:ring-indigo-500',
        ],
    };
@endphp

@if (filled($popup))
    <div
        x-data="{ open: false }"
        x-init="$nextTick(() => { open = true; $nextTick(() => $refs.confirm && $refs.confirm.focus()) })"
        x-show="open"
        x-cloak
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="flash-popup-title"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-0 bg-gray-900/70 backdrop-blur-sm"
            @click="open = false"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-6 scale-95"
            class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5"
        >
            <div class="p-6 sm:p-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $theme['icon'] }}">
                        @if ($type === 'success')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        @elseif ($type === 'error' || $type === 'warning')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM12 3.75 1.5 21h21L12 3.75Z" />
                            </svg>
                        @else
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <h2 id="flash-popup-title" class="text-base font-semibold text-gray-900">
                            {{ $popup['title'] ?? 'Pemberitahuan' }}
                        </h2>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">
                            {{ $popup['message'] ?? '' }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="open = false"
                        class="-m-1 shrink-0 rounded-full p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Tutup"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex justify-end bg-gray-50 px-6 py-4">
                <button
                    type="button"
                    x-ref="confirm"
                    @click="open = false"
                    class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $theme['button'] }}"
                >
                    Mengerti
                </button>
            </div>
        </div>
    </div>
@endif
