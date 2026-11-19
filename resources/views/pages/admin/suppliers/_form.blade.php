@php
    $isEdit = $supplier->exists;
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <x-wms.card>
        <div>
            <h3 class="text-sm font-semibold text-slate-900">Data pemasok</h3>
            <p class="mt-1 text-xs text-slate-500">Pemasok dipakai sebagai asal barang pada purchase order.</p>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <x-wms.field label="Kode pemasok" for="code" hint="Maksimal 30 karakter.">
                <x-wms.input id="code" name="code" required maxlength="30" value="{{ old('code', $supplier->code) }}" placeholder="SUP-001" />
            </x-wms.field>

            <x-wms.field label="Nama pemasok" for="name">
                <x-wms.input id="name" name="name" required maxlength="150" value="{{ old('name', $supplier->name) }}" placeholder="PT Sumber Teknologi" />
            </x-wms.field>

            <x-wms.field label="Narahubung" for="contact_person">
                <x-wms.input id="contact_person" name="contact_person" maxlength="120" value="{{ old('contact_person', $supplier->contact_person) }}" placeholder="Budi" />
            </x-wms.field>

            <x-wms.field label="Telepon" for="phone">
                <x-wms.input id="phone" name="phone" maxlength="40" value="{{ old('phone', $supplier->phone) }}" placeholder="021-555-0100" />
            </x-wms.field>

            <x-wms.field label="Email" for="email">
                <x-wms.input id="email" name="email" type="email" maxlength="150" value="{{ old('email', $supplier->email) }}" placeholder="sales@pemasok.co.id" />
            </x-wms.field>

            <label class="flex items-center gap-3 self-end rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $supplier->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-slate-700">Pemasok aktif</span>
            </label>

            <x-wms.field label="Alamat" for="address" class="sm:col-span-2">
                <x-wms.textarea id="address" name="address" rows="3" maxlength="500">{{ old('address', $supplier->address) }}</x-wms.textarea>
            </x-wms.field>
        </div>
    </x-wms.card>

    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
        <x-wms.button :href="wms_route('suppliers.index')" variant="secondary">Batal</x-wms.button>
        <x-wms.button type="submit">{{ $isEdit ? 'Simpan perubahan' : 'Tambah pemasok' }}</x-wms.button>
    </div>
</form>
