<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Dashboard</h2>
            <p class="mt-1 text-sm text-slate-500">Selamat datang kembali, {{ Auth::user()->name }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-900/5">
                <div class="p-6 sm:p-8">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-wider text-blue-700 ring-1 ring-inset ring-blue-100">
                        Ringkasan
                    </span>
                    <h3 class="mt-4 text-2xl font-semibold tracking-tight text-slate-900">Anda berhasil masuk</h3>
                    <p class="mt-2 max-w-xl text-sm leading-relaxed text-slate-500">
                        Gunakan menu di sebelah kiri untuk mengakses dashboard dan pengaturan profil Anda.
                    </p>
                </div>
            </div>

            <a
                href="{{ route('admin.users.index') }}"
                class="group mt-6 flex flex-col rounded-2xl border border-blue-100 bg-white p-6 shadow-sm shadow-blue-900/5 transition hover:border-blue-200 hover:shadow-md sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-4">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-inset ring-blue-100">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Manajemen user</h3>
                        <p class="mt-1 text-sm text-slate-500">Tambah, edit, dan atur role pengguna Rumitech.</p>
                    </div>
                </div>
                <span class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 group-hover:text-blue-500 sm:mt-0">
                    Kelola user
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </span>
            </a>
        </div>
    </div>
</x-app-layout>
