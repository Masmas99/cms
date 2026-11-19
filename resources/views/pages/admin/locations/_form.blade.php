@php
    $isEdit = $location->exists;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-wms.card>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Koordinat bin</h3>
            <p class="mt-1 text-xs text-slate-500">Kode lengkap dibentuk otomatis: gudang-zona-aisle-rak-bin.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-wms.field label="Gudang" for="warehouse_id" class="sm:col-span-2">
                <x-wms.select id="warehouse_id" name="warehouse_id" required>
                    <option value="">Pilih gudang</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected((int) old('warehouse_id', $location->warehouse_id) === $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                    @endforeach
                </x-wms.select>
            </x-wms.field>

            <x-wms.field label="Zona" for="zone">
                <x-wms.input id="zone" name="zone" required maxlength="20" value="{{ old('zone', $location->zone) }}" placeholder="A" />
            </x-wms.field>

            <x-wms.field label="Aisle" for="aisle">
                <x-wms.input id="aisle" name="aisle" required maxlength="20" value="{{ old('aisle', $location->aisle) }}" placeholder="01" />
            </x-wms.field>

            <x-wms.field label="Rak" for="rack">
                <x-wms.input id="rack" name="rack" required maxlength="20" value="{{ old('rack', $location->rack) }}" placeholder="R1" />
            </x-wms.field>

            <x-wms.field label="Bin" for="bin_code">
                <x-wms.input id="bin_code" name="bin_code" required maxlength="20" value="{{ old('bin_code', $location->bin_code) }}" placeholder="A1" />
            </x-wms.field>

            <x-wms.field label="Kapasitas (m3)" for="capacity_m3">
                <x-wms.input id="capacity_m3" name="capacity_m3" type="number" step="0.01" min="0" required value="{{ old('capacity_m3', $location->capacity_m3 ?? 0) }}" />
            </x-wms.field>

            <x-wms.field label="Kapasitas (kg)" for="capacity_kg">
                <x-wms.input id="capacity_kg" name="capacity_kg" type="number" step="0.01" min="0" required value="{{ old('capacity_kg', $location->capacity_kg ?? 0) }}" />
            </x-wms.field>

            <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700">Bin aktif</span>
            </label>
        </div>
    </x-wms.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-wms.button :href="wms_route('locations.index')" variant="secondary">Batal</x-wms.button>
        <x-wms.button type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah bin' }}</x-wms.button>
    </div>
</form>
