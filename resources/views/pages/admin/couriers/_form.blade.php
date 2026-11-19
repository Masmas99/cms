@php
    $isEdit = $courier->exists;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-wms.card>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Data ekspedisi</h3>
            <p class="mt-1 text-xs text-slate-500">Ekspedisi dipilih saat pengiriman sales order.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <x-wms.field label="Kode ekspedisi" for="code">
                <x-wms.input id="code" name="code" required maxlength="30" value="{{ old('code', $courier->code) }}" placeholder="JNE" />
            </x-wms.field>

            <x-wms.field label="Nama ekspedisi" for="name">
                <x-wms.input id="name" name="name" required maxlength="150" value="{{ old('name', $courier->name) }}" placeholder="JNE Express" />
            </x-wms.field>

            <x-wms.field label="URL pelacakan" for="tracking_url" hint="Opsional, contoh https://jne.co.id/tracking." class="sm:col-span-2">
                <x-wms.input id="tracking_url" name="tracking_url" type="url" maxlength="255" value="{{ old('tracking_url', $courier->tracking_url) }}" placeholder="https://" />
            </x-wms.field>

            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $courier->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700">Ekspedisi aktif</span>
            </label>
        </div>
    </x-wms.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-wms.button :href="wms_route('couriers.index')" variant="secondary">Batal</x-wms.button>
        <x-wms.button type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah ekspedisi' }}</x-wms.button>
    </div>
</form>
