<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Put-away {{ $order->reference_no }}</h2>
                <x-wms.status status="staged" label="Barang di staging" />
            </div>
            <p class="mt-1 text-sm text-slate-500">Pindahkan barang dari staging ke bin penyimpanan {{ $order->warehouse?->name }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Put-away belum dapat disimpan:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ wms_route('purchase-orders.put-away.store', $order) }}" class="space-y-6">
                @csrf

                <x-wms.card :padded="false">
                    <div class="border-b border-slate-100 p-5 sm:p-6">
                        <h3 class="text-sm font-semibold text-slate-900">Tentukan bin tujuan</h3>
                        <p class="mt-1 text-xs text-slate-500">Bin dengan stok paling sedikit disarankan lebih dahulu.</p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($items as $item)
                            <div class="grid gap-4 p-5 sm:grid-cols-12 sm:items-end sm:p-6">
                                <div class="sm:col-span-5">
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->product?->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->product?->sku }} | batch {{ $item->batch_number }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ number_format($item->qty_good, 2, ',', '.') }} {{ $item->product?->uom }}
                                        @if ($item->expired_date)
                                            | ED {{ $item->expired_date->format('d/m/Y') }}
                                        @endif
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">Saat ini di {{ $item->location?->full_code }}</p>
                                </div>

                                <div class="sm:col-span-7">
                                    <label for="location_{{ $item->id }}" class="block text-xs font-semibold text-slate-600">Bin tujuan</label>
                                    <select
                                        id="location_{{ $item->id }}"
                                        name="items[{{ $item->id }}][location_id]"
                                        class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                    >
                                        <option value="">Lewati baris ini</option>
                                        @foreach ($locations as $location)
                                            <option value="{{ $location->id }}" @selected((int) old('items.'.$item->id.'.location_id', $suggestions[$item->id] ?? null) === $location->id)>
                                                {{ $location->full_code }} | stok {{ number_format((float) $location->qty_on_hand, 2, ',', '.') }} | kapasitas {{ number_format($location->capacity_m3, 2, ',', '.') }} m3
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-wms.card>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    Baris yang dilewati tetap berada di staging dan dapat diproses pada put-away berikutnya.
                </div>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('purchase-orders.show', $order)" variant="secondary">Nanti saja</x-wms.button>
                    <x-wms.button type="submit">Proses put-away</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
