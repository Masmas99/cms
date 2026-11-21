<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Manajemen User</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola akun, role, dan akses pengguna Rumitech.</p>
            </div>

            <a
                href="{{ route('admin.users.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah user
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div
            x-data="{
                deleteFormOpen: false,
                deleteAction: '',
                deleteUserName: '',
                confirmDelete(action, name) {
                    this.deleteAction = action;
                    this.deleteUserName = name;
                    this.deleteFormOpen = true;
                },
            }"
            x-init="$watch('deleteFormOpen', value => {
                document.body.classList.toggle('overflow-y-hidden', value);

                if (value) {
                    $nextTick(() => $refs.cancelDelete?.focus());
                }
            })"
            class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8"
        >
            <form method="GET" action="{{ route('admin.users.index') }}" class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm shadow-blue-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>

                        <input
                            type="search"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Cari nama atau email..."
                            aria-label="Cari nama atau email"
                            class="w-full rounded-xl border-blue-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                        >
                    </div>

                    <select
                        name="role"
                        aria-label="Filter role"
                        class="rounded-xl border-blue-200 bg-white py-2.5 pl-3 pr-8 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                    >
                        <option value="">Semua role</option>
                        <option value="{{ \App\Models\User::ROLE_ADMIN }}" @selected($role === \App\Models\User::ROLE_ADMIN)>Admin</option>
                        <option value="{{ \App\Models\User::ROLE_USER }}" @selected($role === \App\Models\User::ROLE_USER)>User</option>
                    </select>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                    >
                        Cari
                    </button>

                    @if ($search !== '' || $role !== '')
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-900/5">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500">
                        <span class="font-semibold text-slate-900">{{ $users->total() }}</span> pengguna
                    </p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Daftar akun</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th scope="col" class="px-6 py-4">Pengguna</th>
                                <th scope="col" class="px-6 py-4">Role</th>
                                <th scope="col" class="px-6 py-4">Bergabung</th>
                                <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($users as $user)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            @php($avatarUrl = $user->avatarUrl())

                                            @if ($avatarUrl)
                                                <img
                                                    src="{{ $avatarUrl }}"
                                                    alt="{{ $user->name }}"
                                                    class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ring-blue-100"
                                                >
                                            @else
                                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-sky-500 text-sm font-semibold text-white">
                                                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                                </span>
                                            @endif
                                            <div class="min-w-0">
                                                <p class="truncate font-medium text-slate-900">
                                                    {{ $user->name }}
                                                    @if ($user->is(Auth::user()))
                                                        <span class="ml-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-blue-700">Anda</span>
                                                    @endif
                                                </p>
                                                <p class="truncate text-xs text-slate-500">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($user->isAdmin())
                                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-blue-700 ring-1 ring-inset ring-blue-100">Admin</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider text-slate-600 ring-1 ring-inset ring-slate-200">User</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        {{ $user->created_at?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-wms.icon-button
                                                :href="route('admin.users.edit', $user)"
                                                icon="pencil"
                                                label="Edit {{ $user->name }}"
                                                variant="primary"
                                            />

                                            @if (! $user->is(Auth::user()))
                                                <x-wms.icon-button
                                                    icon="trash"
                                                    label="Hapus {{ $user->name }}"
                                                    variant="danger"
                                                    @click="confirmDelete(@js(route('admin.users.destroy', $user)), @js($user->name))"
                                                />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <p class="text-sm font-medium text-slate-900">Belum ada pengguna</p>
                                        <p class="mt-1 text-sm text-slate-500">Tambahkan pengguna pertama untuk mulai mengelola akses Rumitech.</p>
                                        <a
                                            href="{{ route('admin.users.create') }}"
                                            class="mt-4 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                        >
                                            Tambah user
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

            <div
                x-show="deleteFormOpen"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                aria-labelledby="delete-user-title"
                aria-describedby="delete-user-description"
                @keydown.escape.window="deleteFormOpen = false"
            >
                <div
                    x-show="deleteFormOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
                    @click="deleteFormOpen = false"
                ></div>

                <div
                    x-show="deleteFormOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative w-full max-w-md overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl shadow-blue-950/20"
                >
                    <div class="p-6 sm:p-7">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1 pt-0.5">
                                <h2 id="delete-user-title" class="text-base font-semibold text-slate-900">Hapus user?</h2>
                                <p id="delete-user-description" class="mt-2 text-sm leading-relaxed text-slate-500">
                                    Akun <span class="font-semibold text-slate-900" x-text="deleteUserName"></span> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-blue-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            x-ref="cancelDelete"
                            @click="deleteFormOpen = false"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Batal
                        </button>

                        <form method="POST" :action="deleteAction" @submit="deleteFormOpen = false">
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-red-600/20 transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:w-auto"
                            >
                                Hapus user
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
