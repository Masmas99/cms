<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Mode scan</h2>
                <p class="mt-1 text-sm text-slate-500">Cari produk, bin, atau sales order dari satu kode.</p>
            </div>

            <x-wms.button :href="wms_route('stocks.index')" variant="secondary">Buka daftar stok</x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card>
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-3.75A1.125 1.125 0 0 1 3.75 8.625v-3.75ZM3.75 15.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-3.75a1.125 1.125 0 0 1-1.125-1.125v-3.75ZM15.375 3.75c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-3.75A1.125 1.125 0 0 1 10.5 8.625v-3.75c0-.621.504-1.125 1.125-1.125h3.75ZM14.25 14.25h3.375m-3.375 3h3.375m-3.375-6h3.375" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-semibold text-slate-900">Arahkan scanner ke kolom di bawah</h3>
                    <p class="mt-1 text-sm text-slate-500">Scanner USB akan mengirim kode dan menekan enter secara otomatis.</p>
                </div>

                <form method="POST" action="{{ wms_route('scan.lookup') }}" class="mt-6">
                    @csrf

                    <label for="code" class="sr-only">Kode scan</label>
                    <input
                        id="code"
                        name="code"
                        value="{{ old('code') }}"
                        autofocus
                        autocomplete="off"
                        placeholder="Scan di sini"
                        class="w-full rounded-2xl border-blue-200 bg-blue-50/40 px-5 py-4 text-center text-lg font-semibold tracking-wide text-slate-900 shadow-sm placeholder:font-normal placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/30"
                    >

                    <x-wms.button type="submit" class="mt-4 w-full">Cari kode</x-wms.button>
                </form>
            </x-wms.card>

            <x-wms.card>
                <h3 class="text-sm font-semibold text-slate-900">Kode yang dikenali</h3>
                <div class="mt-4 space-y-3">
                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-[11px] font-bold text-blue-700 ring-1 ring-blue-100">01</span>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Barcode atau SKU produk</p>
                            <p class="text-xs text-slate-500">Membuka detail produk beserta stok tersedia.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-[11px] font-bold text-blue-700 ring-1 ring-blue-100">02</span>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Kode bin</p>
                            <p class="text-xs text-slate-500">Contoh A-01-02, membuka isi bin tersebut.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                        <span class="mt-0.5 inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-lg bg-white text-[11px] font-bold text-blue-700 ring-1 ring-blue-100">03</span>
                        <div>
                            <p class="text-sm font-medium text-slate-800">Nomor sales order</p>
                            <p class="text-xs text-slate-500">Langsung membuka pesanan untuk picking atau packing.</p>
                        </div>
                    </div>
                </div>
            </x-wms.card>

            @if (! empty($recent))
                <x-wms.card :padded="false">
                    <div class="border-b border-slate-100 px-5 py-4">
                        <h3 class="text-sm font-semibold text-slate-900">Riwayat scan</h3>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @foreach ($recent as $entry)
                            <div class="flex items-center justify-between gap-4 px-5 py-3">
                                <p class="text-sm font-medium text-slate-700">{{ $entry['code'] ?? '-' }}</p>
                                <p class="text-xs text-slate-400">{{ $entry['at'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </x-wms.card>
            @endif
        </div>
    </div>
</x-app-layout>
