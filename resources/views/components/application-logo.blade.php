@props(['alt' => 'Rumitech'])

@php
    $logoVersion = filemtime(public_path('images/logo.png'));
@endphp

<img src="{{ asset('images/logo.png') }}?v={{ $logoVersion }}" alt="{{ $alt }}" {{ $attributes->merge(['class' => 'block']) }}>
