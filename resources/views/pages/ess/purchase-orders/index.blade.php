<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Purchase order</h2>
                <p class="mt-1 text-sm text-slate-500">Jadwal kedatangan barang dan progres penerimaan.</p>
            </div>

            @if (Auth::user()->isAdmin())
                <x-wms.button :href="wms_route('purchase-orders.create')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Purchase order baru
                </x-wms.button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ wms_route('purchase-orders.index') }}" class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm shadow-blue-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Cari referensi atau pemasok..." class="w-full rounded-xl border-blue-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <select name="status" class="rounded-xl border-blue-200 bg-white py-2.5 pl-3 pr-8 text-sm text-slate-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30">
                        <option value="">Semua status</option>
                        @foreach (['scheduled' => 'Terjadwal', 'receiving' => 'Receiving', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $value => $label)
                            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>

                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cari</button>

                    @if ($search !== '' || $status !== '')
                        <a href="{{ wms_route('purchase-orders.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900">Reset</a>
                    @endif
                </div>
            </form>

            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $orders->total() }}</span> purchase order</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Inbound</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Referensi</th>
                                <th class="px-6 py-4">Pemasok</th>
                                <th class="px-6 py-4">Gudang</th>
                                <th class="px-6 py-4">Kedatangan</th>
                                <th class="px-6 py-4 text-right">Baris</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($orders as $order)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <a href="{{ wms_route('purchase-orders.show', $order) }}" class="font-medium text-slate-900 hover:text-blue-700">{{ $order->reference_no }}</a>
                                        <p class="text-xs text-slate-500">{{ $order->created_at?->format('d/m/Y') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $order->supplier?->name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $order->warehouse?->name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">{{ $order->expected_date?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $order->items_count }}</td>
                                    <td class="px-6 py-4"><x-wms.status :status="$order->status" /></td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <x-wms.icon-button :href="wms_route('purchase-orders.show', $order)" icon="eye" label="Detail {{ $order->reference_no }}" />
                                            @if (in_array($order->status, ['scheduled', 'receiving'], true))
                                                <x-wms.icon-button :href="wms_route('purchase-orders.receive', $order)" icon="receive" label="Receive {{ $order->reference_no }}" variant="primary" />
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12">
                                        <x-wms.empty
                                            title="Belum ada purchase order"
                                            :description="Auth::user()->isAdmin() ? 'Buat purchase order untuk menjadwalkan kedatangan barang.' : 'Purchase order akan muncul di sini setelah dibuat oleh admin.'"
                                        >
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.button :href="wms_route('purchase-orders.create')">Purchase order baru</x-wms.button>
                                            @endif
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($orders->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">{{ $orders->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
