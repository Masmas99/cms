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
        <div class="flex min-h-screen bg-white lg:h-screen lg:overflow-hidden">
            <!-- Banner -->
            <aside class="relative hidden overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-indigo-900 lg:block lg:h-full lg:w-1/2 xl:w-[55%]">
                @if ($bannerFile)
                    <img src="{{ asset($bannerFile) }}" alt="" class="absolute inset-0 h-full w-full object-cover object-center">
                @endif
            </aside>

            <!-- Form -->
            <main class="relative flex flex-1 flex-col overflow-hidden">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-50 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-violet-50 blur-3xl" aria-hidden="true"></div>

                <div class="relative flex flex-1 px-6 py-8 sm:px-10 lg:overflow-y-auto lg:py-5">
                    <div class="m-auto w-full max-w-[26rem]">
                        <a href="{{ url('/') }}" aria-label="Kembali ke halaman utama" class="mb-5 inline-flex items-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/25">
                                <x-application-logo class="h-6 w-6 fill-current" />
                            </span>
                        </a>

                        @if ($title)
                            <div class="mb-5">
                                <h1 class="text-3xl font-semibold tracking-tight text-slate-900">{{ $title }}</h1>
                                @if ($subtitle)
                                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $subtitle }}</p>
                                @endif
                            </div>
                        @endif

                        {{ $slot }}

                        @isset($footer)
                            <div class="mt-5 border-t border-slate-200 pt-4 text-center text-sm text-slate-500">
                                {{ $footer }}
                            </div>
                        @endisset
                    </div>
                </div>
            </main>
        </div>

        <x-flash-popup />
    </body>
</html>
