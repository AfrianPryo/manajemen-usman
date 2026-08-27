@props(['label' => '', 'name' => '', 'type' => 'text'])

<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    @endif
    <input type="{{ $type }}" wire:model="{{ $name }}"
           {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm']) }}>
    @error($name)
        <p class="text-xs text-blue-900 mt-1">{{ $message }}</p>
    @enderror
</div>
