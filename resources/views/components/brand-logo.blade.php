{{--
    Logo branding untuk panel kiri halaman auth (login & ganti password).

    Mengikuti identitas sekolah yang diatur Master Admin di Pengaturan >
    Fitur & Modul (Setting 'app_logo' / 'app_name'), sama persis dengan logo
    navbar di landing page (lihat landing.blade.php). Selama admin belum
    mengunggah logo, tampil logo bawaan SIMS.Usaha seperti sebelumnya.

    Logo unggahan dibungkus pil solid (bukan mix-blend seperti logo bawaan)
    karena belum tentu monokrom -- supaya tetap terbaca di atas panel biru.
--}}
@props(['variant' => 'panel'])

@php
    $__brandName = \App\Models\Setting::get('app_name');
    $__brandLogo = \App\Models\Setting::get('app_logo');
@endphp

@if ($variant === 'card')
    {{-- Varian kartu (login, ganti password, setup nomor WA): identitas yang sama dengan sidebar
         dashboard & navbar landing -- logo + nama aplikasi dari Pengaturan Master Admin.
         Logo unggahan dibungkus kotak putih supaya terbaca di mode terang maupun gelap,
         apa pun warna dasar logonya. Selama admin belum mengunggah logo, tampil logo
         bawaan SIMS.Usaha (pasangan light/dark, TANPA mix-blend karena latar kartu selalu
         putih/gelap solid -- mix-blend di sini membalik warna logo jadi krem pucat). --}}
    @if ($__brandLogo)
        <div class="flex max-w-full flex-col items-center gap-2.5">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-md border border-neutral-200 bg-white p-2">
                <img src="{{ asset('storage/' . $__brandLogo) }}" alt="{{ $__brandName ?: 'SIMS' }}" class="h-full w-full object-contain">
            </span>
            @if ($__brandName)
                <span class="max-w-full truncate text-sm font-bold tracking-tight text-neutral-900 dark:text-white">{{ $__brandName }}</span>
            @endif
        </div>
    @else
        <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="block h-7 w-auto dark:hidden">
        <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="hidden h-7 w-auto dark:block">
    @endif
@else
@if ($__brandLogo)
    <span class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full bg-white/95 dark:bg-slate-900/90 shadow-sm shadow-black/10 backdrop-blur">
        <span class="h-6 w-6 rounded-full overflow-hidden shrink-0 flex items-center justify-center bg-white">
            <img src="{{ asset('storage/' . $__brandLogo) }}" alt="{{ $__brandName ?: 'SIMS' }}" class="h-full w-full object-contain">
        </span>
        <span class="text-slate-900 dark:text-white text-[13px] font-bold tracking-tight leading-none truncate max-w-[160px]">{{ $__brandName ?: 'SIMS' }}</span>
    </span>
@else
    {{-- Logo untuk mode terang --}}
    <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto hidden dark:block">

    {{-- Logo untuk mode gelap --}}
    <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto block dark:hidden">
@endif
@endif