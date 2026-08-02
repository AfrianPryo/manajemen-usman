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
    <div class="absolute inset-0 bg-black/40" @click="show = false"></div>

    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4" x-show="show" x-transition>
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-800">{{ $title }}</h3>
            <button @click="show = false" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="px-6 py-4 space-y-4">
            {{ $slot }}
        </div>
        @isset($footer)
            <div class="px-6 py-4 border-t bg-gray-50 rounded-b-xl flex justify-end gap-2">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
