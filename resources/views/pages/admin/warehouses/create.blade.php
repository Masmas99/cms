<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Tambah gudang</h2>
            <p class="mt-1 text-sm text-slate-500">Area staging untuk receiving akan dibuat otomatis setelah gudang disimpan.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @include(wms_view('warehouses._form'), [
                'action' => wms_route('warehouses.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
</x-app-layout>
