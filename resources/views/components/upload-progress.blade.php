{{--
    Bar progres unggah berkas (persentase real-time), pengganti visual
    untuk teks statis "Mengunggah..." pada <input type="file"> yang
    terikat wire:model. Progresnya didapat dari event bawaan Livewire
    (livewire-upload-start/progress/finish/cancel/error) lewat Alpine.js,
    tanpa JS tambahan di luar ini.

    PENTING -- komponen ini WAJIB diletakkan di dalam elemen pembungkus
    yang sudah diberi atribut Alpine berikut (langsung di elemen yang
    membungkus <input type="file"> terkait), karena event upload
    Livewire di-dispatch pada elemen <input>-nya lalu bubbling ke atas:

        x-data="{ uploading: false, progress: 0 }"
        x-on:livewire-upload-start="uploading = true; progress = 0"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-cancel="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"

    Pemakaian:
        <div x-data="{ uploading: false, progress: 0 }" x-on:livewire-upload-start="..." ...>
            <input type="file" wire:model="avatar" ...>
            <x-upload-progress label="Mengunggah foto..." />
        </div>

    Props:
    - label : teks yang tampil selagi proses unggah berlangsung (opsional)
--}}
@props(['label' => 'Mengunggah...'])

<div x-show="uploading" x-cloak class="mt-1.5" role="status" aria-live="polite">
    <div class="flex items-center justify-between text-[11px] font-medium text-amber-600 dark:text-amber-400 mb-1">
        <span>{{ $label }}</span>
        <span x-text="progress + '%'"></span>
    </div>
    <div class="w-full h-1.5 bg-neutral-200 dark:bg-slate-700 rounded-full overflow-hidden">
        <div
            class="h-full bg-blue-900 dark:bg-blue-600 rounded-full transition-all duration-150 ease-out"
            x-bind:style="`width: ${progress}%`"
        ></div>
    </div>
</div>
