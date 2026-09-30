{{--
    Phone number input: pemilih negara (bendera + kode) + nomor terformat otomatis.
    Nilai yang dikirim ke Livewire SELALU format E.164, mis. "+6281234567890".

    Pemakaian:
        x-phone-input wire:model="phone" :value="$phone"

    Untuk menandai error validasi, beri class "phone-invalid" pada elemen pembungkus
    DI LUAR komponen ini (komponen memakai wire:ignore sehingga tidak ikut ter-render
    ulang oleh Livewire). Lihat change-password.blade.php.

    Props:
        value    : nilai awal (format apa pun: 0812.., 62812.., +62812..)
        default  : kode ISO negara default (default "ID")
        id       : id elemen input nomor (untuk atribut for pada label)

    Catatan: bendera memakai gambar dari flagcdn.com (emoji bendera TIDAK tampil
    di Windows/Chrome -- hanya muncul huruf "ID", "MY", dst). Kalau gambar gagal
    dimuat (offline), otomatis jatuh ke kode negara 2 huruf.
--}}
@props([
    'value' => '',
    'default' => 'ID',
    'id' => 'phone',
])

@php
    // Urutan penting: US harus sebelum CA (keduanya +1), Indonesia paling atas.
    $countries = [
        ['iso' => 'ID', 'name' => 'Indonesia', 'dial' => '62'],
        ['iso' => 'MY', 'name' => 'Malaysia', 'dial' => '60'],
        ['iso' => 'SG', 'name' => 'Singapura', 'dial' => '65'],
        ['iso' => 'BN', 'name' => 'Brunei', 'dial' => '673'],
        ['iso' => 'TH', 'name' => 'Thailand', 'dial' => '66'],
        ['iso' => 'PH', 'name' => 'Filipina', 'dial' => '63'],
        ['iso' => 'VN', 'name' => 'Vietnam', 'dial' => '84'],
        ['iso' => 'TL', 'name' => 'Timor Leste', 'dial' => '670'],
        ['iso' => 'AU', 'name' => 'Australia', 'dial' => '61'],
        ['iso' => 'NZ', 'name' => 'Selandia Baru', 'dial' => '64'],
        ['iso' => 'JP', 'name' => 'Jepang', 'dial' => '81'],
        ['iso' => 'KR', 'name' => 'Korea Selatan', 'dial' => '82'],
        ['iso' => 'CN', 'name' => 'Tiongkok', 'dial' => '86'],
        ['iso' => 'HK', 'name' => 'Hong Kong', 'dial' => '852'],
        ['iso' => 'TW', 'name' => 'Taiwan', 'dial' => '886'],
        ['iso' => 'IN', 'name' => 'India', 'dial' => '91'],
        ['iso' => 'PK', 'name' => 'Pakistan', 'dial' => '92'],
        ['iso' => 'BD', 'name' => 'Bangladesh', 'dial' => '880'],
        ['iso' => 'SA', 'name' => 'Arab Saudi', 'dial' => '966'],
        ['iso' => 'AE', 'name' => 'Uni Emirat Arab', 'dial' => '971'],
        ['iso' => 'QA', 'name' => 'Qatar', 'dial' => '974'],
        ['iso' => 'KW', 'name' => 'Kuwait', 'dial' => '965'],
        ['iso' => 'EG', 'name' => 'Mesir', 'dial' => '20'],
        ['iso' => 'TR', 'name' => 'Turki', 'dial' => '90'],
        ['iso' => 'ZA', 'name' => 'Afrika Selatan', 'dial' => '27'],
        ['iso' => 'GB', 'name' => 'Inggris', 'dial' => '44'],
        ['iso' => 'DE', 'name' => 'Jerman', 'dial' => '49'],
        ['iso' => 'FR', 'name' => 'Prancis', 'dial' => '33'],
        ['iso' => 'NL', 'name' => 'Belanda', 'dial' => '31'],
        ['iso' => 'IT', 'name' => 'Italia', 'dial' => '39'],
        ['iso' => 'ES', 'name' => 'Spanyol', 'dial' => '34'],
        ['iso' => 'US', 'name' => 'Amerika Serikat', 'dial' => '1'],
        ['iso' => 'CA', 'name' => 'Kanada', 'dial' => '1'],
        ['iso' => 'BR', 'name' => 'Brasil', 'dial' => '55'],
    ];
@endphp

<div
    wire:ignore
    x-data="phoneInput({ countries: @js($countries), initial: @js((string) $value), defaultIso: @js($default) })"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    {{-- Nilai E.164 yang benar-benar dikirim ke Livewire --}}
    <input type="hidden" x-ref="hidden" value="{{ $value }}" {{ $attributes->whereStartsWith('wire:model') }}>

    <div
        class="phone-input-box flex items-stretch rounded-sm border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 transition-colors focus-within:ring-2 focus-within:ring-blue-500/10 focus-within:border-blue-400"
    >
        {{-- Pemilih negara --}}
        <button
            type="button"
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="listbox"
            aria-label="Pilih kode negara"
            class="flex items-center gap-1.5 pl-3 pr-2 border-r border-neutral-200 dark:border-slate-700 hover:bg-neutral-50 dark:hover:bg-slate-800 rounded-l-sm focus:outline-none transition-colors"
        >
            <img
                :src="'https://flagcdn.com/w40/' + country.iso.toLowerCase() + '.png'"
                :alt="country.name"
                width="20" height="14"
                class="h-3.5 w-5 rounded-[2px] object-cover shadow-[0_0_0_1px_rgba(0,0,0,0.08)]"
                x-on:error="$el.style.visibility = 'hidden'"
            >
            <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-200 tabular-nums" x-text="'+' + country.dial"></span>
            <x-heroicon-o-chevron-down class="h-3 w-3 text-neutral-400" stroke-width="2.5" />
        </button>

        {{-- Nomor --}}
        <input
            x-ref="input"
            id="{{ $id }}"
            type="tel"
            inputmode="tel"
            autocomplete="tel-national"
            :value="formatted"
            :placeholder="placeholder"
            @input="onInput($event)"
            class="min-w-0 flex-1 bg-transparent py-2.5 px-3 text-sm tabular-nums text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none"
        >
    </div>

    {{-- Dropdown negara --}}
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        class="absolute left-0 z-30 mt-1 w-full max-w-xs rounded-sm border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-lg"
    >
        <div class="p-2 border-b border-neutral-100 dark:border-slate-800">
            <input
                x-ref="search"
                x-model="search"
                @keydown.arrow-down.prevent="onListKey($event)"
                @keydown.arrow-up.prevent="onListKey($event)"
                @keydown.enter.prevent="onListKey($event)"
                type="text"
                placeholder="Cari negara atau kode..."
                class="w-full rounded-sm border border-neutral-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-2.5 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:border-blue-400"
            >
        </div>
        <ul x-ref="list" role="listbox" class="max-h-56 overflow-y-auto py-1">
            <template x-for="(c, i) in filtered" :key="c.iso">
                <li
                    role="option"
                    :aria-selected="c.iso === iso"
                    :data-active="i === active"
                    @click="selectCountry(c)"
                    @mouseenter="active = i"
                    class="flex cursor-pointer items-center gap-2.5 px-3 py-1.5 text-xs"
                    :class="i === active ? 'bg-blue-50 dark:bg-slate-800' : ''"
                >
                    <img
                        :src="'https://flagcdn.com/w40/' + c.iso.toLowerCase() + '.png'"
                        alt="" width="20" height="14" loading="lazy"
                        class="h-3.5 w-5 rounded-[2px] object-cover shadow-[0_0_0_1px_rgba(0,0,0,0.08)]"
                        x-on:error="$el.style.visibility = 'hidden'"
                    >
                    <span class="flex-1 truncate text-neutral-800 dark:text-neutral-100" :class="c.iso === iso ? 'font-semibold' : ''" x-text="c.name"></span>
                    <span class="tabular-nums text-neutral-400" x-text="'+' + c.dial"></span>
                </li>
            </template>
            <li x-show="filtered.length === 0" class="px-3 py-3 text-center text-xs text-neutral-400">Negara tidak ditemukan.</li>
        </ul>
    </div>
</div>