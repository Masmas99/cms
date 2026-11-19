@props(['label', 'value', 'hint' => null, 'icon' => null, 'tone' => 'blue'])

@php
    $tones = [
        'blue' => 'bg-blue-50 text-blue-600 ring-blue-100',
        'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        'slate' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-blue-100 bg-white p-5 shadow-sm shadow-blue-900/5']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ $label }}</p>
            <p class="mt-2 truncate text-2xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
            @if (filled($hint))
                <p class="mt-1 text-xs text-slate-500">{{ $hint }}</p>
            @endif
        </div>

        @if (filled($icon))
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 ring-inset {{ $tones[$tone] ?? $tones['blue'] }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                </svg>
            </span>
        @endif
    </div>
</div>
