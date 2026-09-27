{{--
    Ikon bantuan kecil (tanda tanya) untuk label/field yang berpotensi
    membingungkan pengguna awam. Diklik (bukan hover saja, supaya tetap
    jalan di HP/tablet) untuk menampilkan penjelasan singkat, lalu bisa
    ditutup dengan klik di luar atau tombol Escape.

    Pemakaian:
    <label class="flex items-center gap-1">
        Label Field
        <x-help-tip text="Penjelasan singkat yang membantu di sini." />
    </label>

    Props:
    - text  : isi penjelasan (string singkat, 1-3 kalimat)
    - align : 'left' (default) atau 'right', mengatur arah bukaan popover
              supaya tidak terpotong di tepi layar/kontainer sempit
--}}
@props(['text' => '', 'align' => 'left'])

<span
    x-data="{ open: false }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative inline-flex shrink-0 align-middle"
>
    <button
        type="button"
        @click.stop="open = !open"
        :aria-expanded="open ? 'true' : 'false'"
        aria-label="Bantuan"
        class="inline-flex items-center justify-center w-3.5 h-3.5 rounded-full text-neutral-400 dark:text-neutral-500 hover:text-blue-700 dark:hover:text-sky-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 transition-colors cursor-help"
    >
        <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @class([
            'absolute z-50 top-full mt-1.5 w-60 p-2.5 text-[11px] leading-relaxed font-normal normal-case tracking-normal text-neutral-600 dark:text-neutral-300 bg-white dark:bg-slate-800 border border-neutral-200 dark:border-slate-700 rounded-md shadow-lg shadow-black/5',
            'left-0' => $align === 'left',
            'right-0' => $align === 'right',
        ])
        style="display: none;"
    >
        {{ $text }}{{ $slot }}
    </div>
</span>