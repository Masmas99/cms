<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Under Construction</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
            <!-- Soft background accents -->
            <div class="pointer-events-none absolute -top-40 left-1/2 h-96 w-[46rem] -translate-x-1/2 rounded-full bg-violet-600/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-24 h-80 w-80 rounded-full bg-indigo-600/20 blur-3xl"></div>

            <main class="relative w-full max-w-xl text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-white/5 ring-1 ring-inset ring-white/10 backdrop-blur-sm">
                    <svg class="h-7 w-7 text-violet-300" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                    </svg>
                </div>

                <div class="mt-8">
                    <span class="inline-flex items-center rounded-full border border-violet-400/25 bg-violet-400/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-violet-300">
                        Currently under construction
                    </span>
                </div>

                <h1 class="mt-6 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                    Under Construction
                </h1>

                <p class="mx-auto mt-4 max-w-md text-base leading-relaxed text-slate-400">
                    Kami sedang menyiapkan tampilan dan fitur baru. Silakan kembali lagi nanti.
                </p>

                <div class="mt-10">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/10 bg-white/5 px-5 py-2.5 text-sm font-semibold text-slate-200 backdrop-blur-sm transition hover:border-violet-400/40 hover:bg-violet-500/10 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-slate-950">
                        Masuk ke aplikasi
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>

                <p class="mt-16 text-xs text-slate-600">© {{ date('Y') }}</p>
            </main>
        </div>
    </body>
</html>
