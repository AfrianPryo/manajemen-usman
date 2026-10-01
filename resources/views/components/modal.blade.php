{{--
    Komponen modal reusable, dikontrol via Livewire property boolean.
    Cara pakai di Livewire view:
    <x-modal wire:model="showModal" title="Tambah Kategori">
        ... isi form ...
        <x-slot:footer>
            <button wire:click="save" class="btn-primary">Simpan</button>
        </x-slot:footer>
    </x-modal>
--}}
@props(['title' => ''])

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
>
    <div class="absolute inset-0 bg-neutral-900/50 backdrop-blur-sm" @click="show = false"></div>

    <div class="relative bg-white dark:bg-slate-800 rounded-sm shadow-2xl border border-neutral-200 dark:border-slate-700 w-full max-w-lg mx-4" x-show="show" x-transition>
        <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-100 dark:border-slate-700 bg-neutral-50/50 dark:bg-slate-900/50">
            <h3 class="text-sm font-bold text-neutral-900 dark:text-white">{{ $title }}</h3>
            <button type="button" @click="show = false" aria-label="Tutup" class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 cursor-pointer">
                <x-heroicon-o-x-mark class="size-4" stroke-width="2" />
            </button>
        </div>
        <div class="px-6 py-4 space-y-4 text-gray-700 dark:text-neutral-300">
            {{ $slot }}
        </div>
        @isset($footer)
            <div class="px-6 py-4 border-t border-neutral-100 dark:border-slate-700 bg-neutral-50/50 dark:bg-slate-900/50 flex justify-end gap-2">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>