<x-guest-layout
    title="Lupa kata sandi?"
    subtitle="Masukkan email akun Anda. Kami akan mengirimkan link untuk membuat kata sandi baru."
>
    <!-- Session Status -->
    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="grid gap-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" placeholder="nama@email.com" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full gap-2">
            Kirim link reset
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </x-primary-button>
    </form>

    <x-slot name="footer">
        Ingat kata sandi Anda?
        <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">Masuk</a>
    </x-slot>
</x-guest-layout>
