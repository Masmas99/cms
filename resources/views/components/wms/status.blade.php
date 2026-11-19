@props(['status' => null, 'label' => null])

@php
    $value = strtolower((string) $status);

    $labels = [
        'draft' => 'Draft',
        'scheduled' => 'Terjadwal',
        'receiving' => 'Receiving',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'reserved' => 'Dialokasikan',
        'picking' => 'Picking',
        'packed' => 'Siap kirim',
        'shipped' => 'Dikirim',
        'counting' => 'Menghitung',
        'pending' => 'Menunggu',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        'received' => 'Diterima',
        'staged' => 'Staging',
        'put_away' => 'Put-away',
        'restock' => 'Restock',
        'quarantine' => 'Karantina',
        'scrap' => 'Scrap',
        'good' => 'Baik',
        'damaged' => 'Rusak',
    ];

    $tones = [
        'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'shipped' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'packed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'put_away' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'restock' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'good' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'received' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'pending' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'counting' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'quarantine' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'scheduled' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'staged' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'receiving' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'rejected' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'cancelled' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'scrap' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'damaged' => 'bg-rose-50 text-rose-700 ring-rose-200',
        'draft' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset '.($tones[$value] ?? 'bg-blue-50 text-blue-700 ring-blue-200'),
]) }}>
    {{ $label ?? ($labels[$value] ?? ucfirst($value ?: '-')) }}
</span>
