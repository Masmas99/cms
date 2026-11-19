<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Edit pemasok</h2>
            <p class="mt-1 text-sm text-slate-500">Perbarui data {{ $supplier->code }} - {{ $supplier->name }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include(wms_view('suppliers._form'), [
                'action' => wms_route('suppliers.update', $supplier),
                'method' => 'PUT',
            ])
        </div>
    </div>
</x-app-layout>
