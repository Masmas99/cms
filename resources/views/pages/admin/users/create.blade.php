<x-app-layout>
    <x-slot name="header">
        <div>
            <a
                href="{{ route('admin.users.index') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-blue-600 transition hover:text-blue-500"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Kembali
            </a>
            <h2 class="mt-2 text-xl font-semibold tracking-tight text-slate-900">Tambah User</h2>
            <p class="mt-1 text-sm text-slate-500">Buat akun baru dan tentukan role aksesnya.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-900/5">
                <form method="POST" action="{{ route('admin.users.store') }}" class="p-6 sm:p-8">
                    @csrf

                    @include('pages.admin.users._form')

                    <div class="mt-8 flex flex-col-reverse gap-3 border-t border-blue-100 pt-6 sm:flex-row sm:justify-end">
                        <a
                            href="{{ route('admin.users.index') }}"
                            class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            Simpan user
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
