@props(['label' => '', 'name' => '', 'type' => 'text'])

<div>
    @if($label)
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">{{ $label }}</label>
    @endif
    <input type="{{ $type }}" wire:model="{{ $name }}"
           {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400']) }}>
    @error($name)
        <span class="text-xs text-red-500 mt-0.5 block">{{ $message }}</span>
    @enderror
</div>
