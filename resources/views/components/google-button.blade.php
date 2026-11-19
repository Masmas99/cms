@props(['label' => 'Masuk dengan Google'])

<a href="{{ route('auth.google') }}" {{ $attributes->merge(['class' => 'inline-flex w-full items-center justify-center gap-2.5 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2']) }}>
    <x-google-icon class="h-5 w-5" />
    {{ $label }}
</a>
