@php
    $welcomeType = session('welcome');
    $userName = Auth::user()->name;

    $welcome = match ($welcomeType) {
        'register' => [
            'title' => 'Selamat datang, '.$userName.'!',
            'message' => 'Akun Anda berhasil dibuat. Silakan jelajahi dashboard dan lengkapi profil Anda untuk memulai.',
        ],
        'login' => [
            'title' => 'Selamat datang kembali, '.$userName.'!',
            'message' => 'Senang melihat Anda kembali. Lanjutkan pekerjaan Anda di dashboard.',
        ],
        default => null,
    };
@endphp

@if ($welcome !== null)
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
        role="status"
        aria-live="polite"
    >
        <div class="relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-4 shadow-xl shadow-blue-900/10">
            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-blue-500 to-sky-400" aria-hidden="true"></div>

            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z" />
                    </svg>
                </div>

                <div class="min-w-0 flex-1 pt-0.5">
                    <p class="text-sm font-semibold text-slate-900">{{ $welcome['title'] }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">{{ $welcome['message'] }}</p>
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
