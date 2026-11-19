<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold tracking-tight text-slate-900">Tambah produk</h2>
            <p class="mt-1 text-sm text-slate-500">Lengkapi identitas, aturan rotasi, dan ambang stok produk.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            @include(wms_view('products._form'), [
                'action' => wms_route('products.store'),
                'method' => 'POST',
            ])
        </div>
    </div>
</x-app-layout>
