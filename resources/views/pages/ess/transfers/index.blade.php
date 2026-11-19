<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Transfer stok</h2>
                <p class="mt-1 text-sm text-slate-500">Riwayat perpindahan stok antar bin di dalam gudang.</p>
            </div>

            <x-wms.button :href="wms_route('transfers.create')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                </svg>
                Transfer baru
            </x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $transfers->total() }}</span> transfer</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Mutasi internal</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Referensi</th>
                                <th class="px-6 py-4">Produk</th>
                                <th class="px-6 py-4">Asal</th>
                                <th class="px-6 py-4">Tujuan</th>
                                <th class="px-6 py-4 text-right">Qty</th>
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($transfers as $transfer)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900">{{ $transfer->reference_no }}</p>
                                        <p class="text-xs text-slate-500">{{ $transfer->creator?->name }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-800">{{ $transfer->product?->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $transfer->product?->sku }} | {{ $transfer->batch_number }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $transfer->fromLocation?->full_code }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $transfer->toLocation?->full_code }}</td>
                                    <td class="px-6 py-4 text-right font-semibold text-slate-700">{{ number_format($transfer->quantity, 2, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">{{ $transfer->created_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4"><x-wms.status :status="$transfer->status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada transfer" description="Pindahkan stok antar bin untuk merapikan penempatan.">
                                            <x-wms.button :href="wms_route('transfers.create')">Transfer baru</x-wms.button>
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($transfers->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">{{ $transfers->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
