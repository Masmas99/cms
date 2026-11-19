@props(['type' => 'text'])

<input type="{{ $type }}" {{ $attributes->merge(['class' => 'w-full rounded-xl border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/30 disabled:cursor-not-allowed disabled:bg-slate-50']) }}>
