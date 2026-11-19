<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Stock opname</h2>
                <p class="mt-1 text-sm text-slate-500">Hitung fisik berkala dengan skema blind count.</p>
            </div>

            @if (Auth::user()->isAdmin())
                <x-wms.button :href="wms_route('opnames.create')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Opname baru
                </x-wms.button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $opnames->total() }}</span> sesi opname</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kontrol stok</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Referensi</th>
                                <th class="px-6 py-4">Gudang</th>
                                <th class="px-6 py-4">Zona</th>
                                <th class="px-6 py-4">Jadwal</th>
                                <th class="px-6 py-4 text-right">Baris</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($opnames as $opname)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <a href="{{ wms_route('opnames.show', $opname) }}" class="font-medium text-slate-900 hover:text-blue-700">{{ $opname->reference_no }}</a>
                                        <p class="text-xs text-slate-500">{{ $opname->created_at?->format('d/m/Y H:i') }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $opname->warehouse?->name }}</td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $opname->zone ?: 'Semua zona' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">{{ $opname->scheduled_date?->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-right font-medium text-slate-700">{{ $opname->items_count }}</td>
                                    <td class="px-6 py-4"><x-wms.status :status="$opname->status" /></td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ wms_route('opnames.show', $opname) }}" class="inline-flex items-center rounded-lg px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">Buka</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada stock opname" description="Mulai opname untuk mencocokkan catatan sistem dengan hitung fisik.">
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.button :href="wms_route('opnames.create')">Opname baru</x-wms.button>
                                            @endif
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($opnames->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $opnames->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
