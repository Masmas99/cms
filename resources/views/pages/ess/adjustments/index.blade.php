<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Penyesuaian stok</h2>
                <p class="mt-1 text-sm text-slate-500">Pengajuan koreksi stok dan riwayat approval.</p>
            </div>

            <x-wms.button :href="wms_route('adjustments.create')">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Pengajuan baru
            </x-wms.button>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        open: false,
        mode: 'approve',
        action: '',
        reference: '',
        openDialog(detail) {
            this.mode = detail.mode;
            this.action = detail.action;
            this.reference = detail.reference ?? '';
            this.open = true;
            this.$nextTick(() => this.$refs.note?.focus());
        },
    }" x-on:wms-adjust.window="openDialog($event.detail)" x-effect="document.body.classList.toggle('overflow-y-hidden', open)">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2">
                @foreach (['' => 'Semua', 'pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'] as $value => $label)
                    <a
                        href="{{ wms_route('adjustments.index', array_filter(['status' => $value])) }}"
                        @class([
                            'inline-flex items-center rounded-xl px-3.5 py-2 text-xs font-semibold ring-1 ring-inset transition',
                            'bg-blue-600 text-white ring-blue-600 shadow-sm shadow-blue-600/20' => $status === $value,
                            'bg-white text-slate-600 ring-blue-100 hover:bg-blue-50 hover:text-blue-700' => $status !== $value,
                        ])
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $adjustments->total() }}</span> pengajuan</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kontrol stok</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Referensi</th>
                                <th class="px-6 py-4">Produk</th>
                                <th class="px-6 py-4">Bin</th>
                                <th class="px-6 py-4 text-right">Selisih</th>
                                <th class="px-6 py-4">Alasan</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($adjustments as $adjustment)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900">{{ $adjustment->reference_no }}</p>
                                        <p class="text-xs text-slate-500">{{ $adjustment->created_at?->format('d/m/Y H:i') }}</p>
                                        <p class="text-xs text-slate-400">Oleh {{ $adjustment->requester?->name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="{{ wms_route('products.show', $adjustment->product) }}" class="text-sm font-medium text-slate-800 hover:text-blue-700">{{ $adjustment->product?->name }}</a>
                                        <p class="text-xs text-slate-400">{{ $adjustment->product?->sku }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-medium text-slate-600">{{ $adjustment->location?->full_code }}</p>
                                        <p class="text-xs text-slate-400">Batch {{ $adjustment->batch_number ?: '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right font-semibold {{ $adjustment->quantity_difference >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $adjustment->quantity_difference > 0 ? '+' : '' }}{{ number_format($adjustment->quantity_difference, 2, ',', '.') }}
                                    </td>
                                    <td class="max-w-xs px-6 py-4">
                                        <p class="truncate text-xs text-slate-600" title="{{ $adjustment->reason }}">{{ $adjustment->reason }}</p>
                                        @if ($adjustment->opnameItem?->stockOpname)
                                            <p class="text-xs text-slate-400">Opname {{ $adjustment->opnameItem->stockOpname->reference_no }}</p>
                                        @endif
                                        @if ($adjustment->decision_note)
                                            <p class="mt-1 text-xs text-slate-400">Catatan: {{ $adjustment->decision_note }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4"><x-wms.status :status="$adjustment->status" /></td>
                                    <td class="px-6 py-4">
                                        @if (Auth::user()->isAdmin() && $adjustment->status === 'pending')
                                            <div class="flex items-center justify-end gap-1">
                                                <x-wms.icon-button
                                                    icon="check-circle"
                                                    label="Setujui {{ $adjustment->reference_no }}"
                                                    variant="success"
                                                    @click="$dispatch('wms-adjust', { mode: 'approve', action: @js(wms_route('adjustments.approve', $adjustment)), reference: @js($adjustment->reference_no) })"
                                                />
                                                <x-wms.icon-button
                                                    icon="x-mark"
                                                    label="Tolak {{ $adjustment->reference_no }}"
                                                    variant="danger"
                                                    @click="$dispatch('wms-adjust', { mode: 'reject', action: @js(wms_route('adjustments.reject', $adjustment)), reference: @js($adjustment->reference_no) })"
                                                />
                                            </div>
                                        @else
                                            <div class="text-right text-xs text-slate-400">
                                                {{ $adjustment->approver?->name ? 'Oleh '.$adjustment->approver->name : 'Menunggu admin' }}
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada pengajuan" description="Ajukan koreksi bila hasil hitung fisik berbeda dari catatan sistem.">
                                            <x-wms.button :href="wms_route('adjustments.create')">Pengajuan baru</x-wms.button>
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($adjustments->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">{{ $adjustments->links() }}</div>
                @endif
            </x-wms.card>
        </div>

        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="open = false"></div>

            <div
                x-show="open"
                x-transition
                class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-2xl shadow-blue-950/20"
            >
                <form method="POST" :action="action">
                    @csrf

                    <div class="p-6 sm:p-7">
                        <h2 class="text-base font-semibold text-slate-900" x-text="mode === 'approve' ? 'Setujui penyesuaian?' : 'Tolak penyesuaian?'"></h2>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">
                            Pengajuan <span class="font-semibold text-slate-900" x-text="reference"></span>
                            <span x-text="mode === 'approve' ? ' akan langsung mengubah stok fisik.' : ' akan ditolak tanpa mengubah stok.'"></span>
                        </p>

                        <div class="mt-5">
                            <x-input-label for="decision_note" value="Catatan keputusan" />
                            <textarea
                                id="decision_note"
                                name="decision_note"
                                rows="3"
                                maxlength="1000"
                                x-ref="note"
                                x-bind:required="mode === 'reject'"
                                class="mt-1.5 w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30"
                                placeholder="Alasan keputusan, wajib untuk penolakan."
                            ></textarea>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-blue-100 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                        <button type="button" @click="open = false" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-100 hover:text-slate-900">
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                            x-bind:class="mode === 'approve' ? 'bg-emerald-600 shadow-emerald-600/20 hover:bg-emerald-500 focus:ring-emerald-500' : 'bg-rose-600 shadow-rose-600/20 hover:bg-rose-500 focus:ring-rose-500'"
                            x-text="mode === 'approve' ? 'Setujui' : 'Tolak'"
                        ></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
