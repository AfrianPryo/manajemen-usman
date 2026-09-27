{{--
    Toggle switch — pengganti visual untuk <input type="checkbox"> pada
    preferensi on/off (bukan untuk checkbox seleksi baris tabel/daftar
    multi-pilih, yang tetap memakai checkbox biasa karena itu pola standar).

    Dibangun murni CSS (peer-*), tanpa Alpine/JS tambahan — input aslinya
    tetap <input type="checkbox"> yang disembunyikan (sr-only), jadi
    wire:model / wire:model.live bekerja persis seperti checkbox biasa,
    termasuk saat dipakai dalam checkbox-group (array + value).

    Pemakaian:
        <x-toggle wire:model.live="enableWaNotifications"
            label="Notifikasi WhatsApp"
            description="Kirim pemberitahuan penting ke WhatsApp." />

        dalam checkbox-group (wire:model ke array):
        <x-toggle wire:model="reportRoutineSections" value="{{ $key }}" label="{{ $label }}" />

        label custom lewat slot:
        <x-toggle wire:model="is_active">Unit Usaha Aktif</x-toggle>

    Props:
    - label       : judul singkat (opsional bila pakai slot)
    - description : teks penjelasan kecil di bawah label (opsional)
    - value       : dipakai saat wire:model mengikat ke array (checkbox group)
    - disabled    : nonaktifkan toggle
--}}
@props(['label' => null, 'description' => null, 'value' => null, 'disabled' => false])

<label class="inline-flex items-start gap-3 {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }} select-none">
    <span class="relative inline-flex shrink-0 mt-0.5">
        <input
            type="checkbox"
            @if (! is_null($value)) value="{{ $value }}" @endif
            @disabled($disabled)
            {{ $attributes->except('class')->merge(['class' => 'peer sr-only']) }}
        >
        <span class="block w-9 h-5 rounded-full bg-neutral-200 dark:bg-slate-600 peer-checked:bg-blue-900 dark:peer-checked:bg-blue-700 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500/30 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-slate-800 peer-disabled:opacity-60 transition-colors duration-200"></span>
        <span class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full shadow-sm shadow-black/10 transition-transform duration-200 ease-out peer-checked:translate-x-4"></span>
    </span>

    @if ($label || $description)
        <span class="min-w-0">
            @if ($label)
                <span class="block text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $label }}</span>
            @endif
            @if ($description)
                <span class="block text-[11px] text-neutral-400 mt-0.5">{{ $description }}</span>
            @endif
        </span>
    @elseif ($slot->isNotEmpty())
        <span class="min-w-0 text-xs font-medium text-neutral-700 dark:text-neutral-300">{{ $slot }}</span>
    @endif
</label>