<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Audit aktivitas</h2>
            <p class="mt-1 text-sm text-slate-500">Jejak seluruh permintaan tulis yang dilakukan pengguna.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ wms_route('audit.index') }}" class="rounded-2xl border border-blue-100 bg-white p-4 shadow-sm shadow-blue-900/5">
                <div class="grid gap-3 lg:grid-cols-[minmax(0,2fr)_minmax(0,1.4fr)_minmax(0,1fr)_minmax(0,1fr)_auto] lg:items-end">
                    <x-wms.field label="Aksi" for="search">
                        <x-wms.input id="search" name="search" value="{{ $search }}" placeholder="wms.products.store" />
                    </x-wms.field>

                    <x-wms.field label="Pengguna" for="user">
                        <x-wms.select id="user" name="user">
                            <option value="">Semua pengguna</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected($userId === $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </x-wms.select>
                    </x-wms.field>

                    <x-wms.field label="Dari" for="from">
                        <x-wms.input id="from" name="from" type="date" value="{{ $from }}" />
                    </x-wms.field>

                    <x-wms.field label="Sampai" for="to">
                        <x-wms.input id="to" name="to" type="date" value="{{ $to }}" />
                    </x-wms.field>

                    <x-wms.button type="submit">Terapkan</x-wms.button>
                </div>
            </form>

            <x-wms.card :padded="false">
                <div class="flex items-center justify-between gap-4 border-b border-slate-100 px-6 py-4">
                    <p class="text-sm text-slate-500"><span class="font-semibold text-slate-900">{{ $logs->total() }}</span> entri audit</p>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Kepatuhan</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-left text-sm">
                        <thead class="bg-slate-50/70 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Waktu</th>
                                <th class="px-6 py-4">Pengguna</th>
                                <th class="px-6 py-4">Aksi</th>
                                <th class="px-6 py-4">Metode</th>
                                <th class="px-6 py-4">IP</th>
                                <th class="px-6 py-4 text-right">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($logs as $log)
                                <tr class="align-top hover:bg-slate-50/60">
                                    <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">{{ $log->created_at?->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-slate-800">{{ $log->user?->name ?? 'Sistem' }}</p>
                                        <p class="text-xs text-slate-400">{{ $log->user?->email }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">{{ $log->action }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $log->new_values['method'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-xs text-slate-500">{{ $log->ip_address ?: '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <details class="group inline-block text-left">
                                            <summary
                                                title="Lihat detail"
                                                aria-label="Lihat detail"
                                                class="inline-flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-lg text-blue-600 transition hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 [&::-webkit-details-marker]:hidden"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178ZM15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </summary>
                                            <div class="mt-3 w-80 max-w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-left">
                                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">URL</p>
                                                <p class="mt-1 break-all text-xs text-slate-600">{{ $log->new_values['url'] ?? '-' }}</p>
                                                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Payload</p>
                                                <pre class="mt-1 max-h-48 overflow-auto whitespace-pre-wrap break-all text-xs text-slate-600">{{ json_encode($log->new_values['input'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">User agent</p>
                                                <p class="mt-1 break-all text-xs text-slate-500">{{ $log->user_agent ?: '-' }}</p>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12">
                                        <x-wms.empty title="Belum ada aktivitas" description="Entri audit muncul setelah pengguna melakukan aksi tulis." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">{{ $logs->links() }}</div>
                @endif
            </x-wms.card>
        </div>
    </div>
</x-app-layout>
