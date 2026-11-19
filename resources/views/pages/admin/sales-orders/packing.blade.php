<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-semibold tracking-tight text-slate-900">Packing {{ $order->reference_no }}</h2>
                    <x-wms.status :status="$order->status" />
                </div>
                <p class="mt-1 text-sm text-slate-500">{{ $order->customer_name }} | verifikasi setiap baris sebelum diserahkan ke ekspedisi</p>
            </div>

            <x-wms.button :href="wms_route('sales-orders.show', $order)" variant="secondary">Kembali ke detail</x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Verifikasi barang</h3>
                        <p class="mt-1 text-xs text-slate-500">Cocokkan fisik barang dengan baris di bawah, lalu konfirmasi.</p>
                    </div>
                    @if ($order->status === 'packed')
                        <x-wms.status status="packed" label="Siap dikirim" />
                    @endif
                </div>
            </x-wms.card>

            <x-wms.card>
                <form method="POST" action="{{ wms_route('sales-orders.pack', $order) }}" class="flex flex-col gap-3 sm:flex-row">
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

                    <x-wms.button type="submit">Verifikasi scan</x-wms.button>
                </form>
            </x-wms.card>

            <x-wms.card :padded="false">
                <div class="border-b border-slate-100 p-5 sm:p-6">
                    <h3 class="text-sm font-semibold text-slate-900">Barang ter-pick</h3>
                    <p class="mt-1 text-xs text-slate-500">Baris yang sudah diverifikasi tidak muncul lagi di sini.</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($allocations as $allocation)
                        <div class="grid gap-4 p-5 sm:grid-cols-12 sm:items-center sm:p-6">
                            <div class="sm:col-span-1">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">{{ $allocation->pick_sequence }}</span>
                            </div>

                            <div class="sm:col-span-5">
                                <p class="text-sm font-semibold text-slate-900">{{ $allocation->salesOrderItem->product->name }}</p>
                                <p class="text-xs text-slate-500">{{ $allocation->salesOrderItem->product->sku }} | batch {{ $allocation->inventoryStock?->batch_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">Diambil dari {{ $allocation->inventoryStock?->location?->full_code }}</p>
                            </div>

                            <div class="sm:col-span-3">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Kuantitas</p>
                                <p class="text-sm font-bold text-slate-900">{{ number_format($allocation->quantity, 2, ',', '.') }} {{ $allocation->salesOrderItem->product->uom }}</p>
                            </div>

                            <div class="sm:col-span-3 sm:flex sm:justify-end">
                                <form method="POST" action="{{ wms_route('sales-orders.pack', $order) }}">
                                    @csrf
                                    <input type="hidden" name="allocation" value="{{ $allocation->id }}">
                                    <x-wms.button type="submit" variant="secondary">Verifikasi</x-wms.button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-6">
                            <x-wms.empty title="Semua baris terverifikasi" description="Lanjutkan ke detail pesanan untuk mencatat resi dan pengiriman.">
                                <x-wms.button :href="wms_route('sales-orders.show', $order)">Buka detail pesanan</x-wms.button>
                            </x-wms.empty>
                        </div>
                    @endforelse
                </div>
            </x-wms.card>

            @if ($order->status === 'packed')
                <x-wms.card>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Serahkan ke ekspedisi</h3>
                        <p class="mt-1 text-xs text-slate-500">Pilih ekspedisi dan masukkan nomor resi bila tersedia.</p>
                    </div>

                    <form method="POST" action="{{ wms_route('sales-orders.ship', $order) }}" class="mt-4 grid gap-4 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] sm:items-end">
                        @csrf

                        <x-wms.field label="Ekspedisi" for="courier_id">
                            <x-wms.select id="courier_id" name="courier_id">
                                <option value="">Tanpa ekspedisi</option>
                                @foreach ($couriers as $courier)
                                    <option value="{{ $courier->id }}" @selected((int) old('courier_id') === $courier->id)>{{ $courier->name }}</option>
                                @endforeach
                            </x-wms.select>
                        </x-wms.field>

                        <x-wms.field label="Nomor resi" for="tracking_number">
                            <x-wms.input id="tracking_number" name="tracking_number" maxlength="120" value="{{ old('tracking_number') }}" placeholder="CGK1234567890" />
                        </x-wms.field>

                        <x-wms.button type="submit">Tandai dikirim</x-wms.button>
                    </form>
                </x-wms.card>
            @endif
        </div>
    </div>
</x-app-layout>
