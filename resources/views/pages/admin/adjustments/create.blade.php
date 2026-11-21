<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Pengajuan penyesuaian</h2>
            <p class="mt-1 text-sm text-slate-500">Koreksi stok akan berlaku setelah disetujui admin.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ wms_route('adjustments.store') }}" class="space-y-6">
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Detail koreksi</h3>
                        <p class="mt-1 text-xs text-slate-500">Gunakan nilai positif untuk menambah stok dan negatif untuk mengurangi.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <x-wms.field label="Produk" for="product_id" class="sm:col-span-2">
                            <x-wms.select id="product_id" name="product_id" required>
                                <option value="">Pilih produk</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" @selected(old('product_id') === $product->id)>{{ $product->sku }} - {{ $product->name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Bin" for="location_id" class="sm:col-span-2">
                            <x-wms.select id="location_id" name="location_id" required>
                                <option value="">Pilih bin penyimpanan</option>
                                @foreach ($locations->groupBy(fn ($location) => $location->warehouse?->name ?? 'Tanpa gudang') as $warehouseName => $warehouseLocations)
                                    <optgroup label="{{ $warehouseName }}">
                                        @foreach ($warehouseLocations as $location)
                                            <option value="{{ $location->id }}" @selected(old('location_id') === $location->id)>{{ $location->full_code }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Batch" for="batch_number" hint="Kosongkan bila produk tidak memakai batch.">
                            <x-wms.input id="batch_number" name="batch_number" maxlength="80" value="{{ old('batch_number') }}" placeholder="BATCH-001" />
                        </x-wms.field>

                        <x-wms.field label="Selisih kuantitas" for="quantity_difference" hint="Contoh: -2,5 untuk mengurangi 2,5 unit.">
                            <x-wms.input id="quantity_difference" name="quantity_difference" type="number" step="0.01" required value="{{ old('quantity_difference') }}" placeholder="-2.5" />
                        </x-wms.field>

                        <x-wms.field label="Alasan" for="reason" class="sm:col-span-2" hint="Jelaskan hasil hitung fisik atau penyebab selisih.">
                            <x-wms.textarea id="reason" name="reason" rows="4" required maxlength="1000">{{ old('reason') }}</x-wms.textarea>
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    Pengajuan akan berstatus menunggu sampai admin menyetujui. Stok tidak berubah selama menunggu.
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('adjustments.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Kirim pengajuan</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
