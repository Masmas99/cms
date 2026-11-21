<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight text-slate-900">Pemasok</h2>
                <p class="mt-1 text-sm text-slate-500">Daftar pemasok untuk penerimaan barang masuk.</p>
            </div>

            @if (Auth::user()->isAdmin())
                <x-wms.button :href="wms_route('suppliers.create')">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah pemasok
                </x-wms.button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ wms_route('suppliers.index') }}" class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm shadow-blue-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                        </span>
                        <input type="search" name="search" value="{{ $search }}" placeholder="Cari nama atau kode pemasok..." class="w-full rounded-xl border-blue-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30">
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cari</button>

                    @if ($search !== '')
                        <a href="{{ wms_route('suppliers.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 ring-1 ring-inset ring-slate-200 transition hover:bg-slate-50 hover:text-slate-900">Reset</a>
                    @endif
                </div>
            </form>

            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-blue-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $suppliers->total() }}</span> pemasok</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Rantai pasok</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 text-left text-sm">
                        <thead class="bg-blue-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-500">
                            <tr>
                                <th class="px-6 py-4">Pemasok</th>
                                <th class="px-6 py-4">Kontak</th>
                                <th class="px-6 py-4">Telepon</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse ($suppliers as $supplier)
                                <tr class="transition hover:bg-blue-50/40">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-slate-900">{{ $supplier->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $supplier->code }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm text-slate-700">{{ $supplier->contact_person ?: '-' }}</p>
                                        <p class="text-xs text-slate-400">{{ $supplier->email ?: '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-500">{{ $supplier->phone ?: '-' }}</td>
                                    <td class="px-6 py-4">
                                        <x-wms.status :status="$supplier->is_active ? 'approved' : 'draft'" :label="$supplier->is_active ? 'Aktif' : 'Nonaktif'" />
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.icon-button :href="wms_route('suppliers.edit', $supplier)" icon="pencil" label="Edit {{ $supplier->name }}" variant="primary" />
                                                <x-wms.icon-button
                                                    icon="trash"
                                                    label="Hapus {{ $supplier->name }}"
                                                    variant="danger"
                                                    x-on:click="$dispatch('wms-delete', { action: {{ Js::from(wms_route('suppliers.destroy', $supplier)) }}, name: {{ Js::from($supplier->name) }} })"
                                                />
                                            @else
                                                <span class="text-xs text-slate-400">Lihat</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada pemasok" description="Tambahkan pemasok agar purchase order dapat dibuat.">
                                            @if (Auth::user()->isAdmin())
                                                <x-wms.button :href="wms_route('suppliers.create')">Tambah pemasok</x-wms.button>
                                            @endif
                                        </x-wms.empty>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($suppliers->hasPages())
                    <div class="border-t border-blue-100 px-6 py-4">{{ $suppliers->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>

    @if (Auth::user()->isAdmin())
        <x-wms.delete-popup title="Hapus pemasok?" message="Pemasok berikut akan dihapus permanen:" confirm-label="Hapus pemasok" />
    @endif
</x-app-layout>
