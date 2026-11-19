<x-guest-layout
    title="Buat kata sandi baru"
    subtitle="Masukkan kata sandi baru untuk akun Anda."
>
    <form method="POST" action="{{ route('password.store') }}" class="grid gap-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" value="Kata sandi baru" />
            <x-password-input id="password" name="password" class="mt-2" placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi kata sandi baru" />
            <x-password-input id="password_confirmation" name="password_confirmation" class="mt-2" placeholder="Ulangi kata sandi baru" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="w-full gap-2">
            Simpan kata sandi
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </x-primary-button>
    </form>

    <x-slot name="footer">
        Kembali ke halaman
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">Masuk</a>
    </x-slot>
</x-guest-layout>
