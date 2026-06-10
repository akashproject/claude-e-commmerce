@props(['status'])

@php
    $colors = [
        'pending'    => 'bg-gray-100 text-gray-700',
        'paid'       => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-amber-100 text-amber-700',
        'shipped'    => 'bg-indigo-100 text-indigo-700',
        'delivered'  => 'bg-green-100 text-green-700',
        'cancelled'  => 'bg-red-100 text-red-700',
    ];
    $class = $colors[$status] ?? 'bg-gray-100 text-gray-700';
@endphp

<span {{ $attributes->merge(['class' => "inline-block px-2 py-0.5 rounded text-xs font-medium $class"]) }}>
    {{ ucfirst($status) }}
</span>
