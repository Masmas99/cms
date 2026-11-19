@props(['label', 'for' => null, 'hint' => null])

<div {{ $attributes->merge(['class' => 'space-y-1.5']) }}>
    <x-input-label :for="$for" :value="$label" />

    {{ $slot }}

    @if (filled($hint))
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif

    @if (filled($for))
        <x-input-error :messages="$errors->get($for)" />
    @endif
</div>
