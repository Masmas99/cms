<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}">

        <title>Rumitech · Under Construction</title>

        <meta name="description" content="Rumitech adalah aplikasi WMS untuk mengelola stok, barang masuk, barang keluar, dan operasional gudang.">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Rumitech">
        <meta property="og:locale" content="id_ID">
        <meta property="og:title" content="Rumitech · Warehouse Management System">
        <meta property="og:description" content="Aplikasi WMS untuk mengelola stok, barang masuk, barang keluar, dan operasional gudang.">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('images/banner.webp') }}?v={{ filemtime(public_path('images/banner.webp')) }}">
        <meta property="og:image:width" content="1024">
        <meta property="og:image:height" content="572">
        <meta property="og:image:alt" content="Rumitech Warehouse Management System">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="Rumitech · Warehouse Management System">
        <meta name="twitter:description" content="Aplikasi WMS untuk mengelola stok, barang masuk, barang keluar, dan operasional gudang.">
        <meta name="twitter:image" content="{{ asset('images/banner.webp') }}?v={{ filemtime(public_path('images/banner.webp')) }}">
        <meta name="twitter:image:alt" content="Rumitech Warehouse Management System">

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
                <div class="mx-auto h-16 w-16">
                    <x-application-logo class="h-full w-full rounded-[1.5rem] object-cover shadow-2xl shadow-blue-600/30 ring-1 ring-white/10" />
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

                <p class="mt-16 text-xs text-slate-600">© {{ date('Y') }} Rumitech</p>
            </main>
        </div>
    </body>
</html>
