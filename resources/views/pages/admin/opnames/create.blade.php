<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Stock opname baru</h2>
            <p class="mt-1 text-sm text-slate-500">Sistem akan mengambil snapshot isi bin pada saat opname dibuat.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Opname belum dapat dibuat:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ wms_route('opnames.store') }}" class="space-y-6">
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Cakupan hitung</h3>
                        <p class="mt-1 text-xs text-slate-500">Pilih gudang dan zona. Hanya bin berisi stok yang masuk lembar hitung.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <x-wms.field label="Gudang" for="warehouse_id">
                            <x-wms.select id="warehouse_id" name="warehouse_id" required>
                                <option value="">Pilih gudang</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') === $warehouse->id)>{{ $warehouse->code }} - {{ $warehouse->name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Zona" for="zone" hint="Kosongkan untuk seluruh zona gudang.">
                            <x-wms.select id="zone" name="zone">
                                <option value="">Semua zona</option>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone }}" @selected(old('zone') === $zone)>{{ $zone }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Tanggal hitung" for="scheduled_date">
                            <x-wms.input id="scheduled_date" name="scheduled_date" type="date" required value="{{ old('scheduled_date', now()->toDateString()) }}" />
                        </x-wms.field>

                        <x-wms.field label="Catatan" for="notes" class="sm:col-span-2">
                            <x-wms.textarea id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</x-wms.textarea>
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800">
                    Petugas akan mengisi kuantitas fisik tanpa melihat stok sistem. Selisih otomatis menjadi pengajuan penyesuaian yang perlu disetujui admin.
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('opnames.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Buat opname</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
