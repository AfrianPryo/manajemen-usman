{{--
    Pengganti <select> Unit Usaha saat halaman dibuka dalam scope SATU unit
    (Unit Admin, atau Master Admin yang sedang memantau unit tertentu).
    Pilihannya cuma satu dan tidak boleh diubah, jadi cukup ditampilkan
    sebagai kotak teks read-only. Nilai sebenarnya tetap dikunci di server
    lewat ScopedToUnit (lockUnitScope()), bukan dari input ini.

    Pakai:
        <x-locked-field :value="$units->first()->name" class="px-3.5 py-2 text-xs font-medium" />

    Class dari pemanggil hanya untuk ukuran (padding/teks); warna & border
    sudah diatur di sini supaya seragam di semua form.
--}}
@props(['value' => ''])

<input type="text"
       value="{{ $value }}"
       readonly
       aria-readonly="true"
       title="Terkunci ke unit ini"
       {{ $attributes->merge(['class' => 'w-full truncate border border-neutral-200 dark:border-slate-700 rounded-sm bg-neutral-50 dark:bg-slate-800 text-neutral-500 dark:text-neutral-400 cursor-not-allowed select-none focus:outline-none']) }}>