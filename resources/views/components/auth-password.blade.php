{{--
    Field password untuk halaman auth, dengan tombol tampil/sembunyikan.
    Nama field untuk error diambil dari wire:model, jadi cukup:

        <x-auth-password wire:model="password" id="password" label="Password"
                         autocomplete="current-password" placeholder="••••••••" />

    Atribut lain (mis. aria-required) diteruskan ke <input>.
--}}
@props(['id', 'label', 'autocomplete' => 'current-password', 'placeholder' => ''])

@php
    $field = $attributes->whereStartsWith('wire:model')->first();
@endphp

<div x-data="{ show: false }">
    <label for="{{ $id }}" class="mb-1 block text-xs font-medium text-neutral-600 dark:text-neutral-300">
        {{ $label }}
        <span class="text-red-500">*</span>
    </label>

    <div class="relative flex items-center">
        <input
            {{ $attributes->whereStartsWith('wire:model') }}
            {{ $attributes->whereDoesntStartWith('wire:model') }}
            id="{{ $id }}"
            :type="show ? 'text' : 'password'"
            autocomplete="{{ $autocomplete }}"
            placeholder="{{ $placeholder }}"
            class="@error($field) !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full rounded-sm border border-neutral-200 bg-white py-2.5 pl-3 pr-10 text-sm text-neutral-900 placeholder-neutral-400 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
            aria-invalid="@error($field) true @else false @enderror"
        >
        <button
            type="button"
            @click="show = !show"
            :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'"
            class="absolute right-3 text-neutral-400 hover:text-neutral-600 focus:outline-none dark:hover:text-neutral-200"
            tabindex="-1"
        >
            <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
            <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
        </button>
    </div>

    @error($field) <x-form-error :message="$message" :field="$field" /> @enderror
</div>
