<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Receiving {{ $order->reference_no }}</h2>
                <x-wms.status :status="$order->status" />
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $order->supplier?->name }} | {{ $order->warehouse?->name }} | staging lalu put-away</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-6xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    <p class="font-semibold">Penerimaan belum dapat disimpan:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm text-blue-800">
                Barang baik akan masuk ke area staging gudang {{ $order->warehouse?->name }}. Setelah disimpan, lanjutkan put-away ke bin penyimpanan.
            </div>

            <form method="POST" action="{{ wms_route('purchase-orders.receive.store', $order) }}" class="space-y-6">
                @csrf

                <x-wms.card :padded="false">
                    <div class="border-b border-slate-100 p-5 sm:p-6">
                        <h3 class="text-sm font-semibold text-slate-900">Hitung fisik per baris</h3>
                        <p class="mt-1 text-xs text-slate-500">Masukkan kuantitas baik, kuantitas ditolak, dan informasi batch.</p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach ($order->items as $item)
                            @php
                                $outstanding = $item->outstandingQuantity();
                            @endphp
                            <div class="grid gap-4 p-5 sm:grid-cols-12 sm:items-start sm:p-6">
                                <div class="sm:col-span-3">
                                    <p class="text-sm font-semibold text-slate-900">{{ $item->product->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->product->sku }} | {{ $item->product->uom }}</p>
                                    <p class="mt-2 text-xs text-slate-500">
                                        Diharapkan {{ number_format($item->qty_expected, 2, ',', '.') }}<br>
                                        Sisa {{ number_format($outstanding, 2, ',', '.') }}
                                    </p>
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="good_{{ $item->id }}" class="block text-xs font-semibold text-slate-600">Qty baik</label>
                                    <input
                                        id="good_{{ $item->id }}"
                                        name="items[{{ $item->id }}][qty_good]"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $outstanding }}"
                                        value="{{ old('items.'.$item->id.'.qty_good', 0) }}"
                                        class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                    >
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="rejected_{{ $item->id }}" class="block text-xs font-semibold text-slate-600">Qty ditolak</label>
                                    <input
                                        id="rejected_{{ $item->id }}"
                                        name="items[{{ $item->id }}][qty_rejected]"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $outstanding }}"
                                        value="{{ old('items.'.$item->id.'.qty_rejected', 0) }}"
                                        class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                    >
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="batch_{{ $item->id }}" class="block text-xs font-semibold text-slate-600">Batch</label>
                                    <input
                                        id="batch_{{ $item->id }}"
                                        name="items[{{ $item->id }}][batch_number]"
                                        maxlength="80"
                                        value="{{ old('items.'.$item->id.'.batch_number') }}"
                                        placeholder="Kosong = NO-BATCH"
                                        class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                    >
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="expired_{{ $item->id }}" class="block text-xs font-semibold text-slate-600">Kedaluwarsa</label>
                                    <input
                                        id="expired_{{ $item->id }}"
                                        name="items[{{ $item->id }}][expired_date]"
                                        type="date"
                                        value="{{ old('items.'.$item->id.'.expired_date') }}"
                                        class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-wms.card>

                <x-wms.card>
                    <x-wms.field label="Catatan penerimaan" for="notes">
                        <x-wms.textarea id="notes" name="notes" rows="3" maxlength="1000">{{ old('notes') }}</x-wms.textarea>
                    </x-wms.field>
                </x-wms.card>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-wms.button :href="wms_route('purchase-orders.show', $order)" variant="secondary">Batal</x-wms.button>
                    <x-wms.button type="submit">Simpan penerimaan</x-wms.button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
