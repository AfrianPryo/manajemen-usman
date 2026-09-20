@props(['color' => 'gray'])

@php
    $colors = [
        'gray'   => 'bg-gray-100 dark:bg-slate-700 text-gray-700 dark:text-neutral-200',
        'green'  => 'bg-green-100 dark:bg-emerald-950 text-green-700 dark:text-emerald-400',
        'red'    => 'bg-red-100 dark:bg-rose-950 text-blue-950 dark:text-rose-400',
        'amber'  => 'bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-400',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium '.($colors[$color] ?? $colors['gray'])]) }}>
    {{ $slot }}
</span>
