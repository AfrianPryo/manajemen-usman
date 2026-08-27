@props(['label' => '', 'value' => '', 'color' => 'indigo'])

@php
    $colors = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'green'  => 'bg-green-50 text-green-600',
        'red'    => 'bg-red-50 text-blue-900',
        'amber'  => 'bg-amber-50 text-amber-600',
    ];
@endphp

<div class="bg-white rounded-xl shadow-sm border p-5">
    <div class="flex items-center gap-4">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center {{ $colors[$color] ?? $colors['indigo'] }}">
            {{ $icon ?? '' }}
        </div>
        <div>
            <p class="text-xs text-gray-500">{{ $label }}</p>
            <p class="text-xl font-bold text-gray-800">{{ $value }}</p>
        </div>
    </div>
</div>
