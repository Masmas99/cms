@php
    $user = Auth::user();
    $avatarUrl = $user->avatarUrl();
    $initial = strtoupper(mb_substr($user->name, 0, 1));
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false" class="relative">
    <button
        type="button"
        @click="open = ! open"
        :aria-expanded="open"
        aria-haspopup="true"
        title="Menu profil"
        class="flex items-center gap-2 rounded-full bg-white p-1 pr-2 text-left ring-1 ring-inset ring-blue-100 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 sm:pr-3"
    >
        @if ($avatarUrl)
            <img
                src="{{ $avatarUrl }}"
                alt="{{ $user->name }}"
                class="h-8 w-8 shrink-0 rounded-full object-cover ring-2 ring-white"
            >
        @else
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-xs font-semibold text-white">
                {{ $initial }}
            </span>
        @endif

        <span class="hidden min-w-0 sm:block">
            <span class="block max-w-[9rem] truncate text-xs font-semibold text-slate-800">{{ $user->name }}</span>
            <span class="block text-[10px] font-semibold uppercase tracking-wider text-slate-400">{{ $user->isAdmin() ? 'Admin' : 'Staff' }}</span>
        </span>

        <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute right-0 z-40 mt-2 w-64 overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xl shadow-blue-900/10"
    >
        <div class="border-b border-blue-50 px-4 py-3">
            <p class="truncate text-sm font-semibold text-slate-900">{{ $user->name }}</p>
            <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
        </div>

        <div class="p-1.5">
            <a
                href="{{ route('profile.edit') }}"
                class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                Profil
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-500"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>
