<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Ekspedisi</h2>
                <p class="mt-1 text-sm text-slate-500">Daftar jasa pengiriman untuk pengeluaran barang.</p>
            </div>

            @if (Auth::user()->isAdmin())
                <x-wms.button :href="wms_route('couriers.create')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah ekspedisi
                </x-wms.button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $couriers->total() }}</span> ekspedisi</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Pengiriman</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Ekspedisi</th>
                                <th class="px-6 py-4">Pelacakan</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($couriers as $courier)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900">{{ $courier->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $courier->code }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">
                                        @if ($courier->tracking_url)
                                            <a href="{{ $courier->tracking_url }}" target="_blank" rel="noopener noreferrer" class="font-medium text-blue-600 hover:text-blue-500">Buka pelacakan</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-wms.status :status="$courier->is_active ? 'approved' : 'draft'" :label="$courier->is_active ? 'Aktif' : 'Nonaktif'" />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.icon-button :href="wms_route('couriers.edit', $courier)" icon="pencil" label="Edit {{ $courier->name }}" variant="primary" />
                                                <x-wms.icon-button
                                                    icon="trash"
                                                    label="Hapus {{ $courier->name }}"
                                                    variant="danger"
                                                    x-on:click="$dispatch('wms-delete', { action: {{ Js::from(wms_route('couriers.destroy', $courier)) }}, name: {{ Js::from($courier->name) }} })"
                                                />
                                            @else
                                                <span class="text-xs text-slate-400">Lihat</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada ekspedisi" description="Tambahkan ekspedisi agar sales order dapat dikirim.">
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.button :href="wms_route('couriers.create')">Tambah ekspedisi</x-wms.button>
                                            @endif
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($couriers->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">{{ $couriers->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>

    @if (Auth::user()->isAdmin())
        <x-wms.delete-popup title="Hapus ekspedisi?" message="Ekspedisi berikut akan dihapus permanen:" confirm-label="Hapus ekspedisi" />
    @endif
</x-app-layout>
