@php
    $isDashboard = request()->routeIs('dashboard');
    $isAdminArea = request()->routeIs('admin.dashboard');
    $isAdminUsers = request()->routeIs('admin.users.*');
    $isProfile = request()->routeIs('profile.*');
    $userInitial = strtoupper(mb_substr(Auth::user()->name, 0, 1));
@endphp

<!-- Mobile overlay -->
<div
    x-show="sidebarOpen"
    x-transition.opacity
    @click="sidebarOpen = false"
    class="fixed inset-0 z-40 bg-blue-950/30 backdrop-blur-sm lg:hidden"
    x-cloak
></div>

<!-- Sidebar -->
<aside
    id="app-sidebar"
    :class="{
        'translate-x-0': sidebarOpen,
        '-translate-x-full': ! sidebarOpen,
        'lg:w-20': sidebarCollapsed,
        'lg:px-3': sidebarCollapsed,
    }"
    class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col overflow-hidden border-r border-blue-100 bg-white px-4 py-5 shadow-2xl shadow-blue-900/10 transition-all duration-300 ease-in-out lg:translate-x-0 lg:shadow-none"
    aria-label="Navigasi utama"
>
    <div class="pointer-events-none absolute -left-16 top-20 h-56 w-56 rounded-full bg-blue-200/40 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-16 h-64 w-64 rounded-full bg-sky-200/40 blur-3xl" aria-hidden="true"></div>

    <!-- Brand -->
    <div
        class="relative flex items-center justify-between gap-3"
        :class="sidebarCollapsed ? 'lg:flex-col' : ''"
    >
        <a href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('dashboard') }}" class="group flex min-w-0 items-center gap-3" aria-label="Rumitech Dashboard">
            <x-application-logo class="h-10 w-10 shrink-0 rounded-2xl object-cover shadow-lg shadow-blue-600/20 ring-1 ring-blue-100 transition duration-300 group-hover:scale-105" />
            <span class="min-w-0" x-show="! sidebarCollapsed || ! isDesktop">
                <span class="block truncate text-sm font-semibold tracking-tight text-slate-900">Rumitech</span>
                <span class="block truncate text-[10px] font-semibold uppercase tracking-[0.2em] text-blue-500">Content manager</span>
            </span>
        </a>

        <button
            type="button"
            @click="sidebarCollapsed = ! sidebarCollapsed"
            class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:inline-flex"
            aria-controls="app-sidebar"
            :aria-expanded="sidebarCollapsed ? 'false' : 'true'"
            :aria-label="sidebarCollapsed ? 'Perbesar navigasi' : 'Kecilkan navigasi'"
            :title="sidebarCollapsed ? 'Perbesar navigasi' : 'Kecilkan navigasi'"
        >
            <svg :class="sidebarCollapsed ? 'rotate-180' : ''" class="h-4 w-4 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <button
            type="button"
            @click="sidebarOpen = false"
            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 lg:hidden"
        >
            <span class="sr-only">Tutup menu</span>
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="relative mt-8 flex-1 overflow-y-auto" :class="sidebarCollapsed ? 'lg:mt-4' : ''">
        <p class="px-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400" x-show="! sidebarCollapsed || ! isDesktop">
            Menu utama
        </p>

        <div class="mt-3 space-y-1">
            @unless (Auth::user()->isAdmin())
                <a
                    href="{{ route('dashboard') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-inset ring-blue-100' => $isDashboard,
                        'text-slate-500 hover:bg-blue-50/70 hover:text-blue-700' => ! $isDashboard,
                    ])
                    :class="{
                        'lg:justify-center': sidebarCollapsed,
                        'lg:gap-0': sidebarCollapsed,
                        'lg:px-2': sidebarCollapsed,
                    }"
                    :title="sidebarCollapsed ? 'Dashboard' : null"
                    aria-label="Dashboard"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    <span x-show="! sidebarCollapsed || ! isDesktop">Dashboard</span>

                    @if ($isDashboard)
                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-blue-500" x-show="! sidebarCollapsed || ! isDesktop"></span>
                    @endif
                </a>
            @endunless

            @if (Auth::user()->isAdmin())
                <a
                    href="{{ route('admin.dashboard') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-inset ring-blue-100' => $isAdminArea,
                        'text-slate-500 hover:bg-blue-50/70 hover:text-blue-700' => ! $isAdminArea,
                    ])
                    :class="{
                        'lg:justify-center': sidebarCollapsed,
                        'lg:gap-0': sidebarCollapsed,
                        'lg:px-2': sidebarCollapsed,
                    }"
                    :title="sidebarCollapsed ? 'Panel admin' : null"
                    aria-label="Panel admin"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <span x-show="! sidebarCollapsed || ! isDesktop">Panel admin</span>
                    <span class="ml-auto rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-700" x-show="! sidebarCollapsed || ! isDesktop">
                        Admin
                    </span>
                </a>

                <a
                    href="{{ route('admin.users.index') }}"
                    @class([
                        'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                        'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-inset ring-blue-100' => $isAdminUsers,
                        'text-slate-500 hover:bg-blue-50/70 hover:text-blue-700' => ! $isAdminUsers,
                    ])
                    :class="{
                        'lg:justify-center': sidebarCollapsed,
                        'lg:gap-0': sidebarCollapsed,
                        'lg:px-2': sidebarCollapsed,
                    }"
                    :title="sidebarCollapsed ? 'Manajemen user' : null"
                    aria-label="Manajemen user"
                >
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <span x-show="! sidebarCollapsed || ! isDesktop">Manajemen user</span>

                    @if ($isAdminUsers)
                        <span class="ml-auto h-1.5 w-1.5 rounded-full bg-blue-500" x-show="! sidebarCollapsed || ! isDesktop"></span>
                    @endif
                </a>
            @endif

            <a
                href="{{ route('profile.edit') }}"
                @class([
                    'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                    'bg-blue-50 text-blue-700 shadow-sm ring-1 ring-inset ring-blue-100' => $isProfile,
                    'text-slate-500 hover:bg-blue-50/70 hover:text-blue-700' => ! $isProfile,
                ])
                :class="{
                    'lg:justify-center': sidebarCollapsed,
                    'lg:gap-0': sidebarCollapsed,
                    'lg:px-2': sidebarCollapsed,
                }"
                :title="sidebarCollapsed ? 'Profil' : null"
                aria-label="Profil"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                <span x-show="! sidebarCollapsed || ! isDesktop">Profil</span>

                @if ($isProfile)
                    <span class="ml-auto h-1.5 w-1.5 rounded-full bg-blue-500" x-show="! sidebarCollapsed || ! isDesktop"></span>
                @endif
            </a>
        </div>
    </nav>

    <!-- User -->
    <div class="relative mt-4 border-t border-blue-100 pt-4">
        <div
            class="flex items-center gap-3 rounded-2xl bg-blue-50/70 p-3 ring-1 ring-inset ring-blue-100"
            :class="{
                'lg:justify-center': sidebarCollapsed,
                'lg:p-2': sidebarCollapsed,
            }"
        >
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-sm font-semibold text-white">
                {{ $userInitial }}
            </span>
            <div class="min-w-0 flex-1" x-show="! sidebarCollapsed || ! isDesktop">
                <p class="truncate text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs text-slate-500">{{ Auth::user()->email }}</p>
            </div>
        </div>

        <div class="mt-2 grid grid-cols-2 gap-2" :class="{ 'lg:grid-cols-1': sidebarCollapsed }">
            <a
                href="{{ route('profile.edit') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-blue-100 transition hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :title="sidebarCollapsed ? 'Profil' : null"
                aria-label="Profil"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
                <span x-show="! sidebarCollapsed || ! isDesktop">Profil</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-blue-100 transition hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    :title="sidebarCollapsed ? 'Keluar' : null"
                    aria-label="Keluar"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    <span x-show="! sidebarCollapsed || ! isDesktop">Keluar</span>
                </button>
            </form>
        </div>
    </div>
</aside>
