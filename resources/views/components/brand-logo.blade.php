{{--
    Logo branding untuk panel kiri halaman auth (login & ganti password).

    Mengikuti identitas sekolah yang diatur Master Admin di Pengaturan >
    Fitur & Modul (Setting 'app_logo' / 'app_name'), sama persis dengan logo
    navbar di landing page (lihat landing.blade.php). Selama admin belum
    mengunggah logo, tampil logo bawaan SIMS.Usaha seperti sebelumnya.

    Logo unggahan dibungkus pil solid (bukan mix-blend seperti logo bawaan)
    karena belum tentu monokrom -- supaya tetap terbaca di atas panel biru.
--}}
@php
    $__brandName = \App\Models\Setting::get('app_name');
    $__brandLogo = \App\Models\Setting::get('app_logo');
@endphp

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