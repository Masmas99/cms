<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Sales order baru</h2>
            <p class="mt-1 text-sm text-slate-500">Stok langsung dialokasikan sesuai rotasi produk setelah disimpan.</p>
        </div>
    </x-slot>

    @php
        $initialItems = array_values(old('items', [['product_id' => '', 'qty_ordered' => '']]));
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Periksa kembali data berikut:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ wms_route('sales-orders.store') }}" class="space-y-6" x-data="{ items: @js($initialItems) }">
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Informasi pelanggan</h3>
                        <p class="mt-1 text-xs text-slate-500">Data ini muncul pada packing list dan label pengiriman.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <x-wms.field label="Nama pelanggan" for="customer_name">
                            <x-wms.input id="customer_name" name="customer_name" required maxlength="150" value="{{ old('customer_name') }}" placeholder="PT Mitra Jaringan" />
                        </x-wms.field>

                        <x-wms.field label="Telepon" for="customer_phone">
                            <x-wms.input id="customer_phone" name="customer_phone" maxlength="40" value="{{ old('customer_phone') }}" placeholder="0812-3456-7890" />
                        </x-wms.field>

                        <x-wms.field label="Catatan" for="notes" class="sm:col-span-2">
                            <x-wms.textarea id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</x-wms.textarea>
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <x-wms.card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Baris pesanan</h3>
                            <p class="mt-1 text-xs text-slate-500">Alokasi memakai FIFO atau FEFO sesuai pengaturan produk.</p>
                        </div>

                        <x-wms.button type="button" variant="secondary" x-on:click="items.push({ product_id: '', qty_ordered: '' })">Tambah baris</x-wms.button>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="(item, index) in items" x-bind:key="index">
                            <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                                <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_160px_auto] sm:items-end">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'product_' + index">Produk</label>
                                        <select
                                            x-bind:id="'product_' + index"
                                            x-bind:name="'items[' + index + '][product_id]'"
                                            x-model="item.product_id"
                                            required
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                            <option value="">Pilih produk</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}">{{ $product->sku }} - {{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'qty_' + index">Qty pesanan</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            x-bind:id="'qty_' + index"
                                            x-bind:name="'items[' + index + '][qty_ordered]'"
                                            x-model="item.qty_ordered"
                                            required
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                    </div>

                                    <button
                                        type="button"
                                        x-on:click="items.length > 1 ? items.splice(index, 1) : null"
                                        class="inline-flex h-[42px] items-center justify-center rounded-xl px-3 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                        x-bind:disabled="items.length === 1"
                                        x-bind:class="items.length === 1 ? 'cursor-not-allowed opacity-40' : ''"
                                    >
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-wms.card>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('sales-orders.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Simpan dan alokasikan</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
