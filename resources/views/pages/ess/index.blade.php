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
        </div>
    </div>
</x-app-layout>
