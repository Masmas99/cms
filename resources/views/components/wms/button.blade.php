@props(['variant' => 'primary', 'href' => null, 'type' => 'submit'])

@php
    $variants = [
        'primary' => 'bg-blue-600 text-white shadow-sm shadow-blue-600/20 hover:bg-blue-500 active:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-blue-100 hover:bg-blue-50 hover:text-blue-700 focus:ring-blue-500',
        'danger' => 'bg-rose-600 text-white shadow-sm shadow-rose-600/20 hover:bg-rose-500 active:bg-rose-700 focus:ring-rose-500',
        'ghost' => 'bg-transparent text-slate-500 hover:bg-blue-50 hover:text-blue-700 focus:ring-blue-500',
    ];

    $classes = 'inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 '.($variants[$variant] ?? $variants['primary']);
@endphp

@if (filled($href))
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
