<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Transfer baru</h2>
            <p class="mt-1 text-sm text-slate-500">Pindahkan sebagian atau seluruh lot ke bin penyimpanan lain.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <form
                method="POST"
                action="{{ wms_route('transfers.store') }}"
                class="space-y-6"
                x-data="{ available: {{ (float) ($selectedStock?->availableQuantity() ?? 0) }} }"
            >
                @csrf

                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Sumber dan tujuan</h3>
                        <p class="mt-1 text-xs text-slate-500">Hanya stok tersedia (fisik dikurangi alokasi) yang dapat dipindahkan.</p>
                    </div>

                    <div class="mt-5 space-y-4">
                        <x-wms.field label="Lot stok asal" for="inventory_stock_id">
                            <x-wms.select
                                id="inventory_stock_id"
                                name="inventory_stock_id"
                                required
                                x-on:change="available = parseFloat($event.target.selectedOptions[0]?.dataset.available || 0)"
                            >
                                <option value="" data-available="0">Pilih lot stok</option>
                                @foreach ($stocks as $stock)
                                    <option
                                        value="{{ $stock->id }}"
                                        data-available="{{ (float) $stock->availableQuantity() }}"
                                        @selected((int) old('inventory_stock_id', $selectedStock?->id) === $stock->id)
                                    >
                                        {{ $stock->product?->sku }} | {{ $stock->product?->name }} | {{ $stock->location?->full_code }} | batch {{ $stock->batch_number }} | tersedia {{ number_format($stock->availableQuantity(), 2, ',', '.') }}
                                    </option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <div class="rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3 text-sm text-blue-800">
                            Stok tersedia pada lot terpilih:
                            <span class="font-semibold" x-text="Number(available).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })">0,00</span>
                        </div>

                        <x-wms.field label="Bin tujuan" for="to_location_id">
                            <x-wms.select id="to_location_id" name="to_location_id" required>
                                <option value="">Pilih bin penyimpanan</option>
                                @foreach ($locations->groupBy(fn ($location) => $location->warehouse?->name ?? 'Tanpa gudang') as $warehouseName => $warehouseLocations)
                                    <optgroup label="{{ $warehouseName }}">
                                        @foreach ($warehouseLocations as $location)
                                            <option value="{{ $location->id }}" @selected((int) old('to_location_id') === $location->id)>
                                                {{ $location->full_code }} | kapasitas {{ number_format($location->capacity_m3, 2, ',', '.') }} m3
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Kuantitas" for="quantity" hint="Tidak boleh melebihi stok tersedia.">
                            <x-wms.input id="quantity" name="quantity" type="number" step="0.01" min="0.01" required value="{{ old('quantity') }}" x-bind:max="available > 0 ? available : null" />
                        </x-wms.field>

                        <x-wms.field label="Catatan" for="notes">
                            <x-wms.textarea id="notes" name="notes" rows="3" maxlength="500">{{ old('notes') }}</x-wms.textarea>
                        </x-wms.field>
                    </div>
                </x-wms.card>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('transfers.index')" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Proses transfer</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
