<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Utilisasi kapasitas</h2>
                <p class="mt-1 text-sm text-slate-500">Keterisian bin per gudang dan zona.</p>
            </div>

            <x-wms.button :href="wms_route('reports.capacity.export')" variant="secondary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Unduh CSV
            </x-wms.button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @forelse ($warehouses as $warehouse)
                    @php
                        $utilization = $warehouse->total_bins > 0 ? round($warehouse->occupied_bins / $warehouse->total_bins * 100, 1) : 0;
                    @endphp
                    <x-wms.card>
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $warehouse->name }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $warehouse->code }} | {{ $warehouse->total_bins }} bin penyimpanan</p>
                            </div>
                            <p class="text-lg font-bold text-slate-900">{{ $utilization }}%</p>
                        </div>

                        <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $utilization >= 85 ? 'bg-rose-500' : ($utilization >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $utilization) }}%"></div>
                        </div>

                        <p class="mt-2 text-xs text-slate-500">{{ $warehouse->occupied_bins }} bin terisi dari {{ $warehouse->total_bins }} bin</p>
                    </x-wms.card>
                @empty
                    <div class="sm:col-span-2 xl:col-span-3">
                        <x-wms.empty title="Belum ada gudang" description="Tambahkan gudang dan bin untuk melihat utilisasi." />
                    </div>
                @endforelse
            </div>

            <x-wms.card :padded="false">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 p-5 sm:p-6">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Keterisian per zona</h3>
                        <p class="mt-1 text-xs text-slate-500">Bin staging tidak dihitung dalam kapasitas penyimpanan.</p>
                    </div>
                    <x-wms.status status="approved" label="Kapasitas" />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50/70 text-left text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Gudang</th>
                                <th class="px-6 py-4">Zona</th>
                                <th class="px-6 py-4 text-right">Total bin</th>
                                <th class="px-6 py-4 text-right">Bin terisi</th>
                                <th class="px-6 py-4">Utilisasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($zones as $zone)
                                @php
                                    $percentage = $zone->total_bins > 0 ? round($zone->occupied_bins / $zone->total_bins * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/60">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-800">{{ $zone->warehouse_name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $zone->zone ?: 'Tanpa zona' }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $zone->total_bins }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $zone->occupied_bins }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full {{ $percentage >= 85 ? 'bg-rose-500' : ($percentage >= 60 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ min(100, $percentage) }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-slate-600">{{ $percentage }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada bin" description="Bin penyimpanan diperlukan untuk mengukur kapasitas." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
