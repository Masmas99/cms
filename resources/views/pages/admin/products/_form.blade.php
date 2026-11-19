@php
    $isEdit = $product->exists;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-wms.card>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">Identitas produk</h3>
                <p class="mt-1 text-xs text-slate-500">SKU dan barcode dipakai saat scanning serta pencarian.</p>
            </div>
            <x-wms.status :status="$product->is_active ? 'approved' : 'draft'" :label="$product->is_active ? 'Aktif' : 'Nonaktif'" />
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <x-wms.field label="SKU" for="sku" hint="Kode unik internal, contoh RT-001.">
                <x-wms.input id="sku" name="sku" required maxlength="50" value="{{ old('sku', $product->sku) }}" placeholder="RT-001" />
            </x-wms.field>

            <x-wms.field label="Barcode" for="barcode" hint="Opsional. Boleh kosongkan bila memakai SKU.">
                <x-wms.input id="barcode" name="barcode" maxlength="80" value="{{ old('barcode', $product->barcode) }}" placeholder="8991234567890" />
            </x-wms.field>

            <x-wms.field label="Nama produk" for="name" class="sm:col-span-2">
                <x-wms.input id="name" name="name" required maxlength="150" value="{{ old('name', $product->name) }}" placeholder="Kabel UTP Cat6" />
            </x-wms.field>

            <x-wms.field label="Kategori" for="category">
                <x-wms.input id="category" name="category" maxlength="80" value="{{ old('category', $product->category) }}" placeholder="Jaringan" />
            </x-wms.field>

            <x-wms.field label="Satuan" for="uom">
                <x-wms.input id="uom" name="uom" required maxlength="20" value="{{ old('uom', $product->uom) }}" placeholder="pcs" />
            </x-wms.field>
        </div>
    </x-wms.card>

    <x-wms.card>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Aturan stok</h3>
            <p class="mt-1 text-xs text-slate-500">FIFO memakai tanggal masuk, FEFO memprioritaskan tanggal kedaluwarsa.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-wms.field label="Metode rotasi" for="rotation">
                <x-wms.select id="rotation" name="rotation" required>
                    <option value="FIFO" @selected(old('rotation', $product->rotation) === 'FIFO')>FIFO</option>
                    <option value="FEFO" @selected(old('rotation', $product->rotation) === 'FEFO')>FEFO</option>
                </x-wms.select>
            </x-wms.field>

            <x-wms.field label="Stok minimum" for="min_stock">
                <x-wms.input id="min_stock" name="min_stock" type="number" step="0.01" min="0" required value="{{ old('min_stock', $product->min_stock ?? 0) }}" />
            </x-wms.field>

            <x-wms.field label="Stok maksimum" for="max_stock">
                <x-wms.input id="max_stock" name="max_stock" type="number" step="0.01" min="0" required value="{{ old('max_stock', $product->max_stock ?? 0) }}" />
            </x-wms.field>

            <x-wms.field label="Harga pokok / unit" for="unit_cost" hint="Hanya dipakai untuk laporan nilai aset admin.">
                <x-wms.input id="unit_cost" name="unit_cost" type="number" step="0.01" min="0" required value="{{ old('unit_cost', $product->unit_cost ?? 0) }}" />
            </x-wms.field>

            <x-wms.field label="Berat (kg)" for="weight_kg">
                <x-wms.input id="weight_kg" name="weight_kg" type="number" step="0.01" min="0" value="{{ old('weight_kg', $product->weight_kg) }}" />
            </x-wms.field>

            <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700">Produk aktif</span>
            </label>
        </div>
    </x-wms.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-wms.button :href="wms_route('products.index')" variant="secondary">Batal</x-wms.button>
        <x-wms.button type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah produk' }}</x-wms.button>
    </div>
</form>
