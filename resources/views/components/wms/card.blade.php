@props(['padded' => true])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-sm shadow-blue-900/5']) }}>
    @if ($padded)
        <div class="p-5 sm:p-6">{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
</div>
