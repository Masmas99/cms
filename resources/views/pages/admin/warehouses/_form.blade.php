@php
    $isEdit = $warehouse->exists;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-wms.card>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Identitas gudang</h3>
            <p class="mt-1 text-xs text-slate-500">Kode gudang dipakai sebagai awalan kode bin dan label lokasi.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <x-wms.field label="Kode gudang" for="code" hint="Maksimal 20 karakter, otomatis huruf besar.">
                <x-wms.input id="code" name="code" required maxlength="20" value="{{ old('code', $warehouse->code) }}" placeholder="JKT" />
            </x-wms.field>

            <x-wms.field label="Nama gudang" for="name">
                <x-wms.input id="name" name="name" required maxlength="120" value="{{ old('name', $warehouse->name) }}" placeholder="Gudang Jakarta" />
            </x-wms.field>

            <x-wms.field label="Kota" for="city">
                <x-wms.input id="city" name="city" maxlength="80" value="{{ old('city', $warehouse->city) }}" placeholder="Jakarta" />
            </x-wms.field>

            <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $warehouse->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700">Gudang aktif</span>
            </label>

            <x-wms.field label="Alamat" for="address" class="sm:col-span-2">
                <x-wms.textarea id="address" name="address" rows="3" maxlength="500">{{ old('address', $warehouse->address) }}</x-wms.textarea>
            </x-wms.field>
        </div>
    </x-wms.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-wms.button :href="wms_route('warehouses.index')" variant="secondary">Batal</x-wms.button>
        <x-wms.button type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah gudang' }}</x-wms.button>
    </div>
</form>
