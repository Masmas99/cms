<x-guest-layout
    title="Buat akun baru"
    subtitle="Isi data di bawah ini untuk mulai mengelola konten Anda."
>
    <form method="POST" action="{{ route('register') }}" class="grid gap-5">
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
            <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            <p class="mt-1.5 text-xs text-slate-500">Gunakan minimal 8 karakter.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi" />
            <x-text-input id="password_confirmation" class="mt-2 block w-full" type="password" name="password_confirmation" placeholder="Ulangi kata sandi" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">
            Daftar
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
