@props(['label' => '', 'name' => '', 'options' => []])

<div>
    @if($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">{{ $label }}</label>
    @endif
    <select wire:model="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 dark:border-slate-700 dark:bg-slate-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-400/20 text-sm']) }}>
        <option value="">-- Pilih --</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="text-xs text-blue-900 dark:text-rose-400 mt-1">{{ $message }}</p>
    @enderror
</div>
