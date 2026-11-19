@props(['title' => null, 'subtitle' => null])

@php
    $bannerFile = null;

    foreach (['webp', 'jpg', 'jpeg', 'png'] as $bannerExtension) {
        if (file_exists(public_path("images/auth-banner.{$bannerExtension}"))) {
            $bannerFile = "images/auth-banner.{$bannerExtension}";

            break;
        }
    }
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ? $title.' · '.config('app.name', 'Laravel') : config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="flex min-h-screen bg-slate-50">
            <!-- Banner -->
            <aside class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-900 lg:block lg:w-1/2 xl:w-[55%]">
                @if ($bannerFile)
                    <img src="{{ asset($bannerFile) }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
                @endif
            </aside>

            <!-- Form -->
            <main class="flex flex-1 flex-col">
                <div class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10">
                    <div class="w-full max-w-md">
                        <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-2.5 lg:hidden">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                                <x-application-logo class="h-6 w-6 fill-current" />
                            </span>
                            <span class="text-base font-semibold tracking-tight text-slate-900">{{ config('app.name', 'Laravel') }}</span>
                        </a>

                        @if ($title)
                            <div class="mb-8">
                                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
                                @if ($subtitle)
                                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $subtitle }}</p>
                                @endif
                            </div>
                        @endif

                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/60 sm:p-8">
                            {{ $slot }}
                        </div>

                        @isset($footer)
                            <div class="mt-6 text-center text-sm text-slate-500">
                                {{ $footer }}
                            </div>
                        @endisset
                    </div>
                </div>

                <footer class="px-6 pb-6 text-center text-xs text-slate-400">
                    © {{ date('Y') }} {{ config('app.name', 'Laravel') }} · Semua hak dilindungi.
                </footer>
            </main>
        </div>

        <x-flash-popup />
    </body>
</html>
