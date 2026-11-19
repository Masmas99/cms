<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">Picking {{ $order->reference_no }}</h2>
                    <x-wms.status :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $order->customer_name }} | {{ $pickedCount }} dari {{ $totalCount }} baris selesai</p>
            </div>

            <x-wms.button :href="wms_route('sales-orders.show', $order)" variant="secondary">Kembali ke detail</x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $percent = $totalCount > 0 ? (int) round($pickedCount / $totalCount * 100) : 0;
            @endphp

            <x-wms.card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Progres picking</h3>
                        <p class="mt-1 text-xs text-slate-500">Ikuti urutan rute untuk meminimalkan jarak tempuh.</p>
                    </div>
                    <p class="text-sm font-semibold text-blue-700">{{ $percent }}%</p>
                </div>

                <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-blue-500 transition-all" style="width: {{ $percent }}%"></div>
                </div>
            </x-wms.card>

            <x-wms.card>
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Scan bin atau produk</h3>
                    <p class="mt-1 text-xs text-slate-500">Scanner USB akan langsung mengirim kode ke kolom ini.</p>
                </div>

                <form method="POST" action="{{ wms_route('sales-orders.pick', $order) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                    @csrf

                    <div class="flex-1">
                        <label for="code" class="sr-only">Kode scan</label>
                        <input
                            id="code"
                            name="code"
                            autofocus
                            autocomplete="off"
                            placeholder="Scan label bin, SKU, atau barcode produk"
                            class="w-full rounded-xl border-blue-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                        >
                    </div>

                    <x-wms.button type="submit">Konfirmasi scan</x-wms.button>
                </form>
            </x-wms.card>

            <x-wms.card :padded="false">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h3 class="text-sm font-semibold text-slate-900">Daftar pengambilan</h3>
                    <p class="mt-1 text-xs text-slate-500">Ambil barang sesuai bin dan batch pada setiap baris.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($allocations as $allocation)
                        <div class="grid gap-4 p-5 sm:grid-cols-12 sm:items-center sm:p-6">
                            <div class="sm:col-span-1">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">{{ $allocation->pick_sequence }}</span>
                            </div>

                            <div class="sm:col-span-5">
                                <p class="text-sm font-semibold text-slate-900">{{ $allocation->salesOrderItem->product->name }}</p>
                                <p class="text-xs text-slate-500">{{ $allocation->salesOrderItem->product->sku }}</p>
                                <p class="mt-1 text-xs text-slate-500">Batch {{ $allocation->inventoryStock?->batch_number }}</p>
                            </div>

                            <div class="sm:col-span-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Bin</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $allocation->inventoryStock?->location?->full_code }}</p>
                                @if ($allocation->inventoryStock?->expired_date)
                                    <p class="text-xs text-slate-500">ED {{ $allocation->inventoryStock->expired_date->format('d/m/Y') }}</p>
                                @endif
                            </div>

                            <div class="sm:col-span-3 sm:text-right">
                                <p class="text-sm font-bold text-slate-900">{{ number_format($allocation->quantity, 2, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ $allocation->salesOrderItem->product->uom }}</p>
                            </div>

                            <div class="sm:col-span-12 sm:flex sm:justify-end">
                                <form method="POST" action="{{ wms_route('sales-orders.pick', $order) }}">
                                    @csrf
                                    <input type="hidden" name="allocation" value="{{ $allocation->id }}">
                                    <x-wms.button type="submit" variant="secondary">Konfirmasi ambil</x-wms.button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-6">
                            <x-wms.empty title="Tidak ada baris tersisa" description="Seluruh item sudah di-pick. Lanjutkan ke verifikasi packing.">
                                <x-wms.button :href="wms_route('sales-orders.packing', $order)">Ke packing</x-wms.button>
                            </x-wms.empty>
                        </div>
                    @endforelse
                </div>
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
