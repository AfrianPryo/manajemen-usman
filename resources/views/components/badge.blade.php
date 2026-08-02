@props(['color' => 'gray'])

@php
    $colors = [
        'gray'   => 'bg-gray-100 text-gray-700',
        'green'  => 'bg-green-100 text-green-700',
        'red'    => 'bg-red-100 text-red-700',
        'amber'  => 'bg-amber-100 text-amber-700',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
