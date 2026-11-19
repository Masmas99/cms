<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Retur barang</h2>
                <p class="mt-1 text-sm text-slate-500">Barang kembali dari pelanggan dan menunggu disposisi.</p>
            </div>

            <x-wms.button :href="wms_route('returns.create')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Retur baru
            </x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $returns->total() }}</span> retur</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Reverse logistik</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Referensi</th>
                                <th class="px-6 py-4">Pelanggan</th>
                                <th class="px-6 py-4">Sales order</th>
                                <th class="px-6 py-4 text-right">Baris</th>
                                <th class="px-6 py-4">Diterima oleh</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($returns as $return)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <a href="{{ wms_route('returns.show', $return) }}" class="font-medium text-slate-900 hover:text-blue-700">{{ $return->reference_no }}</a>
                                        <p class="text-xs text-slate-500">{{ $return->created_at?->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-700">{{ $return->customer_name }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $return->salesOrder?->reference_no ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $return->items_count }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $return->receiver?->name ?? '-' }}</td>
                                    <td class="px-6 py-4"><x-wms.status :status="$return->status" /></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ wms_route('returns.show', $return) }}" class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada retur" description="Catat barang kembali agar dapat ditentukan disposisinya.">
                                            <x-wms.button :href="wms_route('returns.create')">Retur baru</x-wms.button>
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($returns->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $returns->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
