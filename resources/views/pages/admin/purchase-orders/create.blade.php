<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Purchase order baru</h2>
            <p class="mt-1 text-sm text-slate-500">Jadwalkan kedatangan barang dari pemasok.</p>
        </div>
    </x-slot>

    @php
        $initialItems = array_values(old('items', [['product_id' => '', 'qty_expected' => '']]));
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

            <form method="POST" action="{{ wms_route('purchase-orders.store') }}" class="space-y-6" x-data="{ items: @js($initialItems) }">
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Informasi order</h3>
                        <p class="mt-1 text-xs text-slate-500">Barang akan diterima di area staging gudang terpilih.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <x-wms.field label="Pemasok" for="supplier_id">
                            <x-wms.select id="supplier_id" name="supplier_id" required>
                                <option value="">Pilih pemasok</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((int) old('supplier_id') === $supplier->id)>{{ $supplier->code }} - {{ $supplier->name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Gudang tujuan" for="warehouse_id">
                            <x-wms.select id="warehouse_id" name="warehouse_id" required>
                                <option value="">Pilih gudang</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected((int) old('warehouse_id') === $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Perkiraan kedatangan" for="expected_date">
                            <x-wms.input id="expected_date" name="expected_date" type="date" required value="{{ old('expected_date', now()->addDays(3)->toDateString()) }}" />
                        </x-wms.field>

                        <x-wms.field label="Catatan" for="notes">
                            <x-wms.input id="notes" name="notes" maxlength="1000" value="{{ old('notes') }}" placeholder="Instruksi khusus, nomor kontrak, dsb." />
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <x-wms.card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Baris barang</h3>
                            <p class="mt-1 text-xs text-slate-500">Setiap produk hanya boleh muncul satu kali.</p>
                        </div>

                        <x-wms.button type="button" variant="secondary" x-on:click="items.push({ product_id: '', qty_expected: '' })">Tambah baris</x-wms.button>
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
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'qty_' + index">Qty diharapkan</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            x-bind:id="'qty_' + index"
                                            x-bind:name="'items[' + index + '][qty_expected]'"
                                            x-model="item.qty_expected"
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
                    <x-wms.button :href="wms_route('purchase-orders.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Simpan purchase order</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
