<x-guest-layout
    title="Masuk ke akun Anda"
    subtitle="Masukkan email dan kata sandi untuk melanjutkan ke dasbor."
>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between gap-4">
                <x-input-label for="password" value="Kata sandi" />

                @if (Route::has('password.request'))
                    <a class="rounded text-xs font-medium text-indigo-600 transition hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('password.request') }}">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <x-password-input id="password" name="password" class="mt-2" placeholder="Masukkan kata sandi" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember_me" class="inline-flex w-fit cursor-pointer items-center gap-3 text-sm text-slate-600">
            <span class="relative inline-flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="peer sr-only" @checked(old('remember'))>
                <span class="block h-5 w-9 rounded-full bg-slate-200 transition peer-checked:bg-indigo-600 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2"></span>
                <span class="pointer-events-none absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
            </span>
            Ingat saya
        </label>

        <x-primary-button class="mt-1 w-full gap-2">
            Masuk
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </x-primary-button>

        <div class="relative" aria-hidden="true">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-slate-200"></div>
            </div>
            <div class="relative flex justify-center">
                <span class="bg-white px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">atau</span>
            </div>
        </div>

        <x-google-button label="Masuk dengan Google" />
    </form>

    <x-slot name="footer">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">Daftar sekarang</a>
    </x-slot>
</x-guest-layout>
