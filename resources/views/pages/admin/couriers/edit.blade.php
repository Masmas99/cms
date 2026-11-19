<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Edit ekspedisi</h2>
            <p class="mt-1 text-sm text-slate-500">Perbarui data {{ $courier->code }} - {{ $courier->name }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @include(wms_view('couriers._form'), [
                'action' => wms_route('couriers.update', $courier),
                'method' => 'PUT',
            ])
        </div>
    </div>
</x-app-layout>
