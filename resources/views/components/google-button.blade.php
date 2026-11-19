@props(['label' => 'Masuk dengan Google'])

<a href="{{ route('auth.google') }}" {{ $attributes->merge(['class' => 'inline-flex w-full items-center justify-center gap-2.5 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-slate-100']) }}>
    <x-google-icon class="h-5 w-5" />
    {{ $label }}
</a>
