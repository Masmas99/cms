@php
    $brandScale = [
        ['step' => 50, 'hex' => '#F4F7FE', 'usage' => 'Latar halaman dan tint ringan'],
        ['step' => 100, 'hex' => '#E4ECFB', 'usage' => 'Border kartu dan ring'],
        ['step' => 200, 'hex' => '#C6D6F6', 'usage' => 'Border input dan hover'],
        ['step' => 300, 'hex' => '#9CB7EC', 'usage' => 'Garis dekoratif dan grafik'],
        ['step' => 400, 'hex' => '#608BE1', 'usage' => 'Aksen non-teks'],
        ['step' => 500, 'hex' => '#2158CA', 'usage' => 'Hover tombol utama'],
        ['step' => 600, 'hex' => '#183E8C', 'usage' => 'Aksi utama dan tautan'],
        ['step' => 700, 'hex' => '#16336F', 'usage' => 'Teks brand di latar terang'],
        ['step' => 800, 'hex' => '#142A57', 'usage' => 'Judul di panel gelap'],
        ['step' => 900, 'hex' => '#12213F', 'usage' => 'Latar panel gelap'],
        ['step' => 950, 'hex' => '#0F172A', 'usage' => 'Teks paling pekat'],
    ];

    $statusColors = [
        ['name' => 'Sukses', 'hex' => '#059669', 'chip' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'note' => 'Aksi berhasil, status aktif'],
        ['name' => 'Peringatan', 'hex' => '#D97706', 'chip' => 'bg-amber-50 text-amber-700 ring-amber-200', 'note' => 'Perlu perhatian, data belum lengkap'],
        ['name' => 'Bahaya', 'hex' => '#DC2626', 'chip' => 'bg-red-50 text-red-700 ring-red-200', 'note' => 'Kesalahan, hapus, akses ditolak'],
        ['name' => 'Info', 'hex' => '#183E8C', 'chip' => 'bg-brand-50 text-brand-700 ring-brand-200', 'note' => 'Panduan netral dan pengumuman'],
    ];

    $typeScale = [
        ['token' => 'text-4xl', 'label' => 'Display', 'size' => '36px / 40px', 'weight' => 'Bold 700', 'sample' => 'Rumitech', 'class' => 'text-4xl font-bold tracking-tight text-brand-900'],
        ['token' => 'text-2xl', 'label' => 'Judul halaman', 'size' => '24px / 32px', 'weight' => 'Semibold 600', 'sample' => 'Manajemen user', 'class' => 'text-2xl font-semibold tracking-tight text-slate-900'],
        ['token' => 'text-lg', 'label' => 'Subjudul', 'size' => '18px / 28px', 'weight' => 'Semibold 600', 'sample' => 'Ringkasan aktivitas', 'class' => 'text-lg font-semibold text-slate-900'],
        ['token' => 'text-sm', 'label' => 'Body', 'size' => '14px / 24px', 'weight' => 'Regular 400', 'sample' => 'Kelola pengguna dan konten Rumitech dari satu tempat.', 'class' => 'text-sm leading-relaxed text-slate-600'],
        ['token' => 'text-sm', 'label' => 'Label', 'size' => '14px / 20px', 'weight' => 'Medium 500', 'sample' => 'Alamat email', 'class' => 'text-sm font-medium text-slate-700'],
        ['token' => 'text-xs', 'label' => 'Keterangan', 'size' => '12px / 16px', 'weight' => 'Semibold 600', 'sample' => 'Terakhir diperbarui', 'class' => 'text-xs font-semibold uppercase tracking-[0.2em] text-slate-400'],
    ];
@endphp

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}">

        <title>Design System · Rumitech</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite('resources/css/app.css')
    </head>
    <body class="bg-brand-50 font-sans text-slate-900 antialiased">
        <header class="border-b border-brand-100 bg-white">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3" aria-label="Rumitech">
                    <img
                        src="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}"
                        alt=""
                        class="h-9 w-9 shrink-0 rounded-control object-cover ring-1 ring-brand-100"
                    />
                    <span class="truncate text-sm font-semibold tracking-tight text-slate-900">Rumitech</span>
                    <span class="hidden text-sm text-slate-400 sm:inline">/ Design system</span>
                </a>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 rounded-control px-3 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Beranda
                </a>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
                <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-600">Panduan merek</p>
                        <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Design system</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">Fondasi visual Rumitech yang diturunkan langsung dari logo.</p>
                    </div>

                    <span class="inline-flex items-center rounded-full bg-brand-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-brand-700 ring-1 ring-inset ring-brand-200">
                        Versi 1.0
                    </span>
                </div>

            {{-- Identitas --}}
            <section class="overflow-hidden rounded-card border border-brand-100 bg-white shadow-card">
                <div class="grid gap-8 p-6 sm:p-8 lg:grid-cols-[auto_1fr] lg:items-center">
                    <div class="flex items-center justify-center rounded-card bg-brand-50 p-6 ring-1 ring-inset ring-brand-100">
                        <img
                            src="{{ asset('images/logo.webp') }}?v={{ filemtime(public_path('images/logo.webp')) }}"
                            alt="Logo Rumitech"
                            class="h-24 w-24 rounded-card object-cover shadow-pop ring-1 ring-brand-100"
                        />
                    </div>

                    <div>
                        <h3 class="text-2xl font-semibold tracking-tight text-brand-900">Satu warna, satu identitas.</h3>
                        <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600">
                            Logo Rumitech hanya memakai dua warna: biru <span class="font-mono text-xs font-semibold text-brand-700">#183E8C</span> dan putih.
                            Design system ini memperluas warna tersebut menjadi skala yang konsisten, lalu memadukannya dengan netral slate dan tipografi Figtree.
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            @foreach ([['#183E8C', 'Primary'], ['#FFFFFF', 'Canvas']] as [$value, $label])
                                <span class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-white px-3 py-1.5 text-xs text-slate-600 shadow-sm">
                                    <span class="h-3.5 w-3.5 rounded-full border border-brand-200" style="background-color: {{ $value }}"></span>
                                    <span class="font-mono text-[11px] font-semibold text-slate-900">{{ $value }}</span>
                                    {{ $label }}
                                </span>
                            @endforeach

                            <span class="inline-flex items-center gap-2 rounded-full border border-brand-100 bg-white px-3 py-1.5 text-xs text-slate-600 shadow-sm">
                                <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full bg-brand-600 text-[8px] font-bold leading-none text-white">Aa</span>
                                <span class="text-[11px] font-semibold text-slate-900">Figtree</span>
                                Typeface
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex h-2" aria-hidden="true">
                    @foreach ($brandScale as $color)
                        <span class="h-full flex-1" style="background-color: {{ $color['hex'] }}"></span>
                    @endforeach
                </div>
            </section>

            {{-- Warna brand --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">01</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Warna brand</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Skala <span class="font-mono text-xs">brand-50</span> sampai <span class="font-mono text-xs">brand-950</span> diturunkan dari biru logo.
                            Gunakan <span class="font-mono text-xs">brand-600</span> untuk aksi utama dan <span class="font-mono text-xs">brand-700</span> untuk teks di latar terang.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach ($brandScale as $color)
                        <div class="overflow-hidden rounded-control border border-brand-100">
                            <div
                                class="flex h-20 flex-col justify-between p-3 {{ $color['step'] >= 500 ? 'text-white' : 'text-brand-900' }}"
                                style="background-color: {{ $color['hex'] }}"
                            >
                                <span class="text-xs font-semibold">{{ $color['step'] }}</span>
                                <span class="font-mono text-[10px] opacity-80">{{ $color['hex'] }}</span>
                            </div>
                            <p class="bg-white px-3 py-2 text-[11px] leading-snug text-slate-500">{{ $color['usage'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Warna status --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">02</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Warna status</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Status memakai palet bawaan Tailwind agar mudah dikenali. Hanya info yang memakai warna brand.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($statusColors as $status)
                        <div class="rounded-control border border-slate-200 bg-white p-4 shadow-sm">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $status['chip'] }}">
                                {{ $status['name'] }}
                            </span>
                            <p class="mt-3 font-mono text-xs font-semibold text-slate-900">{{ $status['hex'] }}</p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-500">{{ $status['note'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Tipografi --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">03</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Tipografi</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Satu keluarga huruf, Figtree, dengan hierarki yang tegas. Berat 600–700 untuk judul dan 400–500 untuk teks isi.
                        </p>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden rounded-control border border-slate-200">
                    <div class="hidden grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-2.5 text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400 sm:grid">
                        <span>Contoh</span>
                        <span>Token</span>
                        <span>Spesifikasi</span>
                    </div>

                    @foreach ($typeScale as $type)
                        <div class="grid gap-2 border-b border-slate-100 px-4 py-4 last:border-b-0 sm:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)] sm:items-center sm:gap-4">
                            <p class="{{ $type['class'] }}">{{ $type['sample'] }}</p>
                            <p class="font-mono text-[11px] font-semibold text-brand-700">{{ $type['token'] }}</p>
                            <p class="text-xs text-slate-500">{{ $type['label'] }} · {{ $type['size'] }} · {{ $type['weight'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Bentuk --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">04</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Bentuk dan elevasi</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Sudut membulat memberi kesan ramah. Elevasi dipakai tipis saja agar antarmuka tetap tenang.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 lg:grid-cols-2">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Radius</p>
                        <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ([['rounded-control', 'control', '0.75rem'], ['rounded-card', 'card', '1rem'], ['rounded-full', 'full', '9999px']] as [$class, $name, $value])
                                <div class="rounded-control border border-slate-200 bg-slate-50 p-3 text-center">
                                    <div class="mx-auto h-12 w-12 border border-brand-200 bg-white {{ $class }}"></div>
                                    <p class="mt-3 font-mono text-[11px] font-semibold text-slate-900">{{ $class }}</p>
                                    <p class="text-[10px] text-slate-500">{{ $value }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Elevasi</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            @foreach ([['shadow-card', 'card', 'Kartu dan panel'], ['shadow-pop', 'pop', 'Floating & popover']] as [$class, $name, $note])
                                <div class="rounded-control border border-slate-200 bg-white p-4 {{ $class }}">
                                    <p class="font-mono text-[11px] font-semibold text-slate-900">{{ $class }}</p>
                                    <p class="mt-1 text-[11px] text-slate-500">{{ $note }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- Komponen --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">05</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Komponen</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Contoh penerapan token pada elemen yang paling sering muncul di aplikasi.
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-8">
                    {{-- Tombol --}}
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Tombol</p>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <button type="button" class="inline-flex items-center justify-center rounded-control bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 active:bg-brand-700">
                                Simpan perubahan
                            </button>
                            <button type="button" class="inline-flex items-center justify-center rounded-control border border-brand-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                Sekunder
                            </button>
                            <button type="button" class="inline-flex items-center justify-center rounded-control px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                Netral
                            </button>
                            <button type="button" class="inline-flex items-center justify-center rounded-control bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                Hapus
                            </button>
                            <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-control bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white opacity-60">
                                Nonaktif
                            </button>
                        </div>
                    </div>

                    <div class="grid gap-8 lg:grid-cols-2">
                        {{-- Form --}}
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Formulir</p>
                            <div class="mt-3 space-y-4">
                                <div>
                                    <label for="design-system-email" class="block text-sm font-medium text-slate-700">Alamat email</label>
                                    <input
                                        id="design-system-email"
                                        type="email"
                                        value="admin@rumitech.id"
                                        class="mt-1.5 block w-full rounded-control border border-brand-200 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                                    />
                                    <p class="mt-1.5 text-xs text-slate-500">Kami hanya memakai email ini untuk notifikasi akun.</p>
                                </div>

                                <div>
                                    <label for="design-system-password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
                                    <input
                                        id="design-system-password"
                                        type="password"
                                        value="rahasia123"
                                        aria-invalid="true"
                                        class="mt-1.5 block w-full rounded-control border border-red-300 bg-red-50/40 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/30"
                                    />
                                    <p class="mt-1.5 text-xs font-medium text-red-600">Kata sandi minimal 8 karakter.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Badge & alert --}}
                        <div>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Label dan notifikasi</p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ([['Admin', 'bg-brand-50 text-brand-700 ring-brand-200'], ['Aktif', 'bg-emerald-50 text-emerald-700 ring-emerald-200'], ['Menunggu', 'bg-amber-50 text-amber-700 ring-amber-200'], ['Ditolak', 'bg-red-50 text-red-700 ring-red-200'], ['Arsip', 'bg-slate-100 text-slate-600 ring-slate-200']] as [$label, $class])
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $class }}">{{ $label }}</span>
                                @endforeach
                            </div>

                            <div class="mt-4 space-y-2.5">
                                <div class="flex items-start gap-3 rounded-control border border-emerald-200 bg-emerald-50 p-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                        </svg>
                                    </span>
                                    <p class="text-xs leading-relaxed text-emerald-800"><span class="font-semibold">User tersimpan.</span> Perubahan role langsung berlaku.</p>
                                </div>

                                <div class="flex items-start gap-3 rounded-control border border-red-200 bg-red-50 p-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM12 3.75 1.5 21h21L12 3.75Z" />
                                        </svg>
                                    </span>
                                    <p class="text-xs leading-relaxed text-red-800"><span class="font-semibold">Gagal menyimpan.</span> Periksa kembali data yang diisi.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kartu --}}
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">Kartu</p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-card border border-brand-100 bg-white p-5 shadow-card">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-xs font-medium text-slate-500">Total pengguna</p>
                                        <p class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">128</p>
                                    </div>
                                    <span class="flex h-10 w-10 items-center justify-center rounded-control bg-brand-50 text-brand-600 ring-1 ring-inset ring-brand-100">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                        </svg>
                                    </span>
                                </div>
                                <p class="mt-3 inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">
                                    +12% bulan ini
                                </p>
                            </div>

                            <div class="rounded-card border border-brand-100 bg-white p-5 shadow-card">
                                <p class="text-xs font-medium text-slate-500">Aksi cepat</p>
                                <p class="mt-1 text-sm leading-relaxed text-slate-600">Kartu memakai border brand-100, radius card, dan shadow-card agar konsisten dengan panel lain.</p>
                                <a href="{{ route('home') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-600 transition hover:text-brand-500">
                                    Kembali ke beranda
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Aturan --}}
            <section class="mt-6 rounded-card border border-brand-100 bg-white p-6 shadow-card sm:p-8">
                <div class="flex items-start gap-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-control bg-brand-50 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-100">06</span>
                    <div>
                        <h3 class="text-lg font-semibold tracking-tight text-slate-900">Aturan pakai</h3>
                        <p class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-500">
                            Batasan singkat agar tampilan tetap konsisten saat fitur baru ditambahkan.
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-control border border-emerald-200 bg-emerald-50/60 p-4">
                        <p class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-800">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            Lakukan
                        </p>
                        <ul class="mt-3 space-y-2 text-xs leading-relaxed text-emerald-900">
                            <li>Pakai satu aksi <span class="font-mono">brand-600</span> per layar agar fokus jelas.</li>
                            <li>Gunakan <span class="font-mono">brand-50</span> untuk latar dan putih untuk permukaan kartu.</li>
                            <li>Pertahankan Figtree untuk seluruh teks aplikasi.</li>
                            <li>Beri jarak lega; utamakan keterbacaan dibanding kepadatan.</li>
                        </ul>
                    </div>

                    <div class="rounded-control border border-red-200 bg-red-50/60 p-4">
                        <p class="inline-flex items-center gap-2 text-sm font-semibold text-red-800">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </span>
                            Hindari
                        </p>
                        <ul class="mt-3 space-y-2 text-xs leading-relaxed text-red-900">
                            <li>Jangan memakai <span class="font-mono">brand-400</span> untuk teks kecil; kontrasnya hanya 3.35:1.</li>
                            <li>Jangan mencampur indigo atau blue bawaan Tailwind dengan palet brand di komponen yang sama.</li>
                            <li>Jangan menambahkan gradien atau bayangan berat pada logo.</li>
                            <li>Jangan memakai lebih dari dua warna aksen dalam satu tampilan.</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-control border border-brand-100 bg-brand-50/60 px-4 py-3">
                    <p class="text-xs text-slate-600">
                        Token didefinisikan di <span class="font-mono font-semibold text-brand-700">tailwind.config.js</span> dalam grup <span class="font-mono font-semibold text-brand-700">theme.extend</span>.
                    </p>
                    <p class="text-xs text-slate-500">
                        Kontras putih di <span class="font-mono text-brand-700">brand-600</span>: <span class="font-semibold text-slate-700">9.97:1</span> (AAA)
                    </p>
                </div>
            </section>
            </div>
        </main>

        <footer class="border-t border-brand-100 bg-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>Design system Rumitech v1.0 · Diturunkan dari logo perusahaan.</p>
                <p class="font-mono text-brand-700">brand-600 · #183E8C</p>
            </div>
        </footer>
    </body>
</html>
