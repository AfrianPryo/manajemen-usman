@props(['label' => '', 'value' => '', 'color' => 'indigo'])

@php
    $colors = [
        'indigo' => 'bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400',
        'green'  => 'bg-green-50 dark:bg-emerald-950 text-green-600 dark:text-emerald-400',
        'red'    => 'bg-red-50 dark:bg-rose-950 text-red-600 dark:text-rose-400',
        'amber'  => 'bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400',
    ];
@endphp

<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-5">
    <div class="flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center {{ $colors[$color] ?? $colors['indigo'] }}">
            {{ $icon ?? '' }}
        </div>
        <div>
            <p class="text-xs text-gray-500 dark:text-neutral-400">{{ $label }}</p>
            <p class="text-xl font-bold text-gray-800 dark:text-white">{{ $value }}</p>
        </div>
    </div>
</div>
