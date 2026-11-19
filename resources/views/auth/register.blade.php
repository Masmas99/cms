<x-guest-layout
    title="Buat akun baru"
    subtitle="Isi data di bawah ini untuk mulai mengelola konten Anda."
>
    <form method="POST" action="{{ route('register') }}" class="grid gap-4">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama" />
            <x-text-input id="name" class="mt-2 block w-full" type="text" name="name" :value="old('name')" placeholder="Nama lengkap" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata sandi" />
            <x-password-input id="password" name="password" class="mt-2" placeholder="Minimal 8 karakter" required autocomplete="new-password" aria-describedby="password-help" />
            <p id="password-help" class="mt-1.5 flex items-center gap-1.5 text-xs text-slate-500">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                Gunakan minimal 8 karakter.
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />
            <x-password-input id="password_confirmation" name="password_confirmation" class="mt-2" placeholder="Ulangi kata sandi" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full gap-2">
            Buat akun
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

        <x-google-button label="Daftar dengan Google" />
    </form>

    <x-slot name="footer">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">Masuk</a>
    </x-slot>
</x-guest-layout>
