<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Edit bin</h2>
            <p class="mt-1 text-sm text-slate-500">Perbarui koordinat dan kapasitas {{ $location->full_code }}.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include(wms_view('locations._form'), [
                'action' => wms_route('locations.update', $location),
                'method' => 'PUT',
            ])
        </div>
    </div>
</x-app-layout>
