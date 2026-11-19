<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}">

        <title>Rumitech · Under Construction</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-brand-950 font-sans text-slate-100 antialiased">
        <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-6 py-16">
            <!-- Gradasi gelap bernuansa brand -->
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-brand-900 via-brand-950 to-black"></div>

            <main class="relative w-full max-w-4xl text-center">
                <a
                    href="{{ config('app.wms_url') }}"
                    class="group mx-auto block w-full max-w-3xl overflow-hidden rounded-card shadow-2xl shadow-brand-950/60 ring-1 ring-white/15 transition hover:-translate-y-0.5 hover:shadow-brand-950/80 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-brand-950"
                    aria-label="Buka aplikasi Rumitech"
                >
                    <img
                        src="{{ asset('images/banner.webp') }}"
                        alt="Rumitech Warehouse Management System"
                        width="1024"
                        height="572"
                        fetchpriority="high"
                        class="h-auto w-full transition duration-500 group-hover:scale-[1.015]"
                    >
                </a>

                <div class="mx-auto mt-8 max-w-lg">
                    <span class="inline-flex items-center rounded-full border border-brand-400/30 bg-brand-400/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-200">
                        Currently under construction
                    </span>

                    <h1 class="mt-5 text-4xl font-semibold tracking-tight text-white sm:text-5xl">
                        Under Construction
                    </h1>

                    <p class="mx-auto mt-4 max-w-md text-base leading-relaxed text-brand-100/80">
                        Kami sedang menyiapkan tampilan dan fitur baru. Silakan kembali lagi nanti.
                    </p>

                    <div class="mt-8">
                        <a href="{{ config('app.wms_url') }}" class="inline-flex items-center justify-center gap-2 rounded-control bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-950/40 transition hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-400 focus:ring-offset-2 focus:ring-offset-brand-950 active:bg-brand-700">
                            Masuk ke aplikasi
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>
                </div>

                <p class="mt-14 text-xs text-brand-200/60">© {{ date('Y') }} Rumitech</p>
            </main>
        </div>
    </body>
</html>
