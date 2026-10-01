{{-- Tambahkan :required="true" untuk field wajib: label diberi tanda * dan select diberi aria-required. --}}
@props(['label' => '', 'name' => '', 'options' => [], 'required' => false])

<div>
    @if($label)
        <label class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    @endif
    <select wire:model="{{ $name }}"
            @if($required) aria-required="true" @endif
            @error($name) aria-invalid="true" @enderror
            {{ $attributes->merge(['class' => 'w-full px-3 py-2.5 border rounded-sm text-sm bg-white dark:bg-slate-900 border-neutral-200 dark:border-slate-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400']) }}>
        <option value="">-- Pilih --</option>
        @foreach($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>
    @error($name)
        <x-form-error :message="$message" :field="$name" />
    @enderror
</div>
