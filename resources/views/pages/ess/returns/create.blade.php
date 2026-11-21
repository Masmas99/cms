<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Retur baru</h2>
            <p class="mt-1 text-sm text-slate-500">Catat barang yang dikembalikan pelanggan beserta kondisinya.</p>
        </div>
    </x-slot>

    @php
        $initialItems = array_values(old('items', [['product_id' => '', 'quantity' => '', 'condition' => 'good', 'batch_number' => '', 'notes' => '']]));
    @endphp

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Retur belum dapat disimpan:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ wms_route('returns.store') }}" class="space-y-6" x-data="{ items: @js($initialItems) }">
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Informasi retur</h3>
                        <p class="mt-1 text-xs text-slate-500">Kaitkan dengan sales order bila retur berasal dari pengiriman tertentu.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <x-wms.field label="Sales order" for="sales_order_id" hint="Opsional.">
                            <x-wms.select id="sales_order_id" name="sales_order_id">
                                <option value="">Tanpa sales order</option>
                                @foreach ($salesOrders as $order)
                                    <option value="{{ $order->id }}" @selected(old('sales_order_id', $salesOrder?->id) === $order->id)>{{ $order->reference_no }} | {{ $order->customer_name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Nama pelanggan" for="customer_name">
                            <x-wms.input id="customer_name" name="customer_name" required maxlength="150" value="{{ old('customer_name', $salesOrder?->customer_name) }}" placeholder="PT Mitra Jaringan" />
                        </x-wms.field>

                        <x-wms.field label="Catatan retur" for="notes" class="sm:col-span-2">
                            <x-wms.textarea id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</x-wms.textarea>
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <x-wms.card>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900">Baris retur</h3>
                            <p class="mt-1 text-xs text-slate-500">Satu produk hanya boleh muncul satu kali per retur.</p>
                        </div>

                        <x-wms.button type="button" variant="secondary" x-on:click="items.push({ product_id: '', quantity: '', condition: 'good', batch_number: '', notes: '' })">Tambah baris</x-wms.button>
                    </div>

                    <div class="mt-5 space-y-3">
                        <template x-for="(item, index) in items" x-bind:key="index">
                            <div class="rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                                <div class="grid gap-3 sm:grid-cols-12 sm:items-end">
                                    <div class="sm:col-span-5">
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

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'qty_' + index">Kuantitas</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0.01"
                                            x-bind:id="'qty_' + index"
                                            x-bind:name="'items[' + index + '][quantity]'"
                                            x-model="item.quantity"
                                            required
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'condition_' + index">Kondisi</label>
                                        <select
                                            x-bind:id="'condition_' + index"
                                            x-bind:name="'items[' + index + '][condition]'"
                                            x-model="item.condition"
                                            required
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                            <option value="good">Baik</option>
                                            <option value="damaged">Rusak</option>
                                        </select>
                                    </div>

                                    <div class="sm:col-span-3">
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'batch_' + index">Batch</label>
                                        <input
                                            x-bind:id="'batch_' + index"
                                            x-bind:name="'items[' + index + '][batch_number]'"
                                            x-model="item.batch_number"
                                            maxlength="80"
                                            placeholder="Opsional"
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                    </div>

                                    <div class="sm:col-span-11">
                                        <label class="block text-xs font-semibold text-slate-600" x-bind:for="'notes_' + index">Catatan baris</label>
                                        <input
                                            x-bind:id="'notes_' + index"
                                            x-bind:name="'items[' + index + '][notes]'"
                                            x-model="item.notes"
                                            maxlength="500"
                                            placeholder="Keluhan atau keterangan kondisi"
                                            class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                        >
                                    </div>

                                    <div class="sm:col-span-1">
                                        <button
                                            type="button"
                                            x-on:click="items.length > 1 ? items.splice(index, 1) : null"
                                            class="inline-flex h-[42px] w-full items-center justify-center rounded-xl px-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                            x-bind:disabled="items.length === 1"
                                            x-bind:class="items.length === 1 ? 'cursor-not-allowed opacity-40' : ''"
                                        >
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-wms.card>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    Retur dicatat sebagai diterima. Admin akan menentukan apakah barang di-restock, dikarantina, atau di-scrap.
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('returns.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Simpan retur</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
