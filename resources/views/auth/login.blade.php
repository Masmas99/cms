<x-guest-layout
    title="Masuk ke akun Anda"
    subtitle="Gunakan email dan kata sandi Anda untuk melanjutkan ke dasbor."
>
    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

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

            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" placeholder="••••••••" required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <label for="remember_me" class="inline-flex w-fit cursor-pointer items-center gap-2.5 text-sm text-slate-600">
            <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
            Ingat saya
        </label>

        <x-primary-button class="w-full">
            Masuk
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
