{{--
    Tombol kecil "Panduan" untuk memutar ulang tutorial kontekstual halaman/aksi
    (komponen <livewire:page-tour> dengan nama tour yang sama harus ada di halaman).

    Pemakaian:  <x-tour-replay tour="documents.generate" />
--}}
@props(['tour', 'label' => 'Panduan'])

<button type="button"
        x-data
        @click="window.dispatchEvent(new CustomEvent('start-tour', { detail: { tour: @js($tour) } }))"
        title="Putar ulang panduan halaman ini"
        {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 hover:text-blue-800 dark:hover:text-sky-400 bg-neutral-100 dark:bg-slate-700/60 hover:bg-blue-50 dark:hover:bg-blue-950/40 rounded-sm transition-all cursor-pointer shrink-0 whitespace-nowrap']) }}>
    <x-heroicon-o-question-mark-circle class="w-3.5 h-3.5" />
    {{ $label }}
</button>
