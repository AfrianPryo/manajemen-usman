{{--
    Komponen form 2-langkah untuk modal dengan field yang banyak.

    Field di kedua tab TETAP ada di DOM, jadi wire:model, validasi Livewire,
    dan file upload tetap berfungsi walau tabnya sedang tersembunyi.

    Ukuran container tetap: kedua panel ditumpuk di satu sel grid dan yang
    tidak aktif hanya di-invisible, jadi tinggi form = tinggi panel terpanjang
    dan tidak "melompat" saat pindah tab.

    Footer (Lanjut / Kembali / Simpan) ada di dalam komponen ini:
      - Tab 1 : [Lanjut]              -> hanya pindah ke tab 2
      - Tab 2 : [Kembali] [Simpan]    -> Kembali ke tab 1, Simpan menyimpan form
    Tidak ada tombol Batal; menutup form lewat ikon X di header modal.
    Tombol submit TIDAK dirender di tab 1, dan Enter di input tab 1 diarahkan
    ke tab 2, jadi form tidak bisa tersimpan sebelum sampai tab terakhir.

    Kalau simpan gagal validasi, komponen membaca nama field (wire:model) di
    tiap tab lalu mencocokkannya dengan $errors: tab yang berisi error diberi
    titik merah dan, kalau tab yang sedang dibuka tidak punya error, otomatis
    pindah ke tab yang punya. Tidak perlu daftar field manual dari parent.

    Props:
      tab1-label / tab2-label : judul tab
      cancel                  : (tidak dipakai lagi, boleh dihapus dari pemanggil)
      compact                 : true untuk ukuran tombol kecil (text-xs)
      rounded                 : kelas radius tombol (default rounded-sm)
      active                  : (opsional) paksa tab awal 1 / 2

    Pemakaian:
        <x-form-tabs tab1-label="Data Utama" tab2-label="Detail">
            <x-slot:tab1> ... </x-slot:tab1>
            <x-slot:tab2> ... </x-slot:tab2>
            <x-slot:submit>
                <button type="submit" wire:loading.attr="disabled" class="...">Simpan</button>
            </x-slot:submit>
        </x-form-tabs>
--}}
@props([
    'tab1Label' => 'Detail',
    'tab2Label' => 'Lainnya',
    'cancel' => null, // tidak dipakai lagi; penutupan lewat ikon X di header modal
    'compact' => false,
    'rounded' => 'rounded-sm',
    'active' => null,
])

@php
    // Nama field per tab, diambil dari atribut wire:model (termasuk .live/.blur/dst).
    // Induk key array ikut dihitung ("items.0.qty" -> "items.0", "items") supaya
    // error level array (misal key 'items') juga menandai tab-nya.
    $modelsIn = function ($slot) {
        if (! preg_match_all('/wire:model(?:\.[\w-]+)*\s*=\s*"([^"]+)"/', (string) $slot, $m)) {
            return [];
        }
        $keys = [];
        foreach ($m[1] as $name) {
            $parts = explode('.', $name);
            for ($i = 1, $n = count($parts); $i <= $n; $i++) {
                $keys[] = implode('.', array_slice($parts, 0, $i));
            }
        }
        return array_values(array_unique($keys));
    };

    $tab1Errors = $errors->hasAny($modelsIn($tab1));
    $tab2Errors = $errors->hasAny($modelsIn($tab2));

    $startTab = $active !== null
        ? ((int) $active === 2 ? 2 : 1)
        : ($tab2Errors && ! $tab1Errors ? 2 : 1);

    $btnBack = $compact
        ? "px-4 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-100 dark:bg-slate-700 {$rounded} hover:bg-neutral-200 dark:hover:bg-slate-600 transition-all cursor-pointer"
        : "px-4 py-2.5 border border-neutral-200 dark:border-slate-700 {$rounded} text-sm font-semibold hover:bg-neutral-50 dark:hover:bg-slate-700 dark:text-white transition-colors cursor-pointer";

    $btnNext = $compact
        ? "inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white bg-blue-900 hover:bg-blue-950 {$rounded} transition-all shadow-sm cursor-pointer"
        : "inline-flex items-center gap-1.5 px-5 py-2.5 text-sm font-semibold text-white bg-blue-900 hover:bg-blue-950 {$rounded} transition-colors shadow-sm shadow-blue-900/20 cursor-pointer";
@endphp

<div x-data="{ formTab: {{ $startTab }}, armed: false }"
     data-err="{{ ($tab1Errors ? '1' : '') . ($tab2Errors ? '2' : '') }}"
     x-init="window.__formTabsSync ||= (window.Livewire && Livewire.hook('commit', ({ succeed }) => succeed(() => setTimeout(() => window.dispatchEvent(new CustomEvent('form-tabs-synced')), 0))), true)"
     x-on:keydown.enter="if (formTab === 1 && $event.target.tagName === 'INPUT') { $event.preventDefault(); formTab = 2 }"
     x-on:form-tabs-synced.window="if (armed) { armed = false; const e = $el.dataset.err; if (e && !e.includes(formTab)) formTab = +e[0] }">

    {{-- Tab Switcher --}}
    <div class="flex items-center gap-5 border-b border-neutral-100 dark:border-slate-700">
        <button type="button" wire:key="form-tab-1" @click="formTab = 1"
                :class="formTab === 1
                    ? 'text-blue-900 dark:text-blue-400 border-blue-900 dark:border-blue-400'
                    : 'text-neutral-400 dark:text-neutral-500 border-transparent hover:text-neutral-600 dark:hover:text-neutral-300'"
                class="flex items-center gap-1.5 pb-2.5 -mb-px text-xs font-bold border-b-2 transition-colors cursor-pointer">
            <span :class="formTab === 1
                    ? 'bg-blue-900 dark:bg-blue-400 text-white dark:text-slate-900'
                    : 'bg-neutral-200 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400'"
                  class="w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center transition-colors shrink-0">1</span>
            <span>{{ $tab1Label }}</span>
            @if($tab1Errors)
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0" title="Ada isian yang perlu diperbaiki"></span>
            @endif
        </button>
        <button type="button" wire:key="form-tab-2" @click="formTab = 2"
                :class="formTab === 2
                    ? 'text-blue-900 dark:text-blue-400 border-blue-900 dark:border-blue-400'
                    : 'text-neutral-400 dark:text-neutral-500 border-transparent hover:text-neutral-600 dark:hover:text-neutral-300'"
                class="flex items-center gap-1.5 pb-2.5 -mb-px text-xs font-bold border-b-2 transition-colors cursor-pointer">
            <span :class="formTab === 2
                    ? 'bg-blue-900 dark:bg-blue-400 text-white dark:text-slate-900'
                    : 'bg-neutral-200 dark:bg-slate-700 text-neutral-500 dark:text-neutral-400'"
                  class="w-4 h-4 rounded-full text-[10px] font-bold flex items-center justify-center transition-colors shrink-0">2</span>
            <span>{{ $tab2Label }}</span>
            @if($tab2Errors)
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0" title="Ada isian yang perlu diperbaiki"></span>
            @endif
        </button>
    </div>

    {{-- Isi tab: kedua panel ditumpuk di satu sel grid, jadi tinggi container selalu
         mengikuti panel yang paling tinggi dan tidak berubah saat pindah tab.
         Panel yang tidak aktif disembunyikan pakai invisible (bukan display:none)
         supaya tetap ikut dihitung tingginya, tapi tidak bisa diklik/difokus. --}}
    <div class="grid grid-cols-1">
        <div x-cloak :class="formTab === 1 ? '' : 'invisible pointer-events-none'"
             :inert="formTab !== 1" :aria-hidden="formTab !== 1"
             class="col-start-1 row-start-1 min-w-0 space-y-4 pt-4">
            {{ $tab1 }}
        </div>

        <div x-cloak :class="formTab === 2 ? '' : 'invisible pointer-events-none'"
             :inert="formTab !== 2" :aria-hidden="formTab !== 2"
             class="col-start-1 row-start-1 min-w-0 space-y-4 pt-4">
            {{ $tab2 }}
        </div>
    </div>

    {{-- Footer: tab 1 = [Lanjut], tab terakhir = [Kembali] [Simpan].
         Membatalkan/menutup form lewat ikon X di header modal (di luar komponen ini). --}}
    <div class="mt-4 pt-4 border-t border-neutral-100 dark:border-slate-700 flex items-center justify-end gap-2.5">
        <button type="button" x-show="formTab === 2" x-cloak @click="formTab = 1" class="inline-flex items-center gap-1.5 {{ $btnBack }}">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            Kembali
        </button>

        <button type="button" x-show="formTab === 1" x-cloak @click="formTab = 2" class="{{ $btnNext }}">
            Lanjut
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </button>

        <div x-show="formTab === 2" x-cloak @click.capture="armed = true" class="contents">
            {{ $submit }}
        </div>
    </div>
</div>
