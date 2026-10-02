@extends('layouts.landing')

@php
    // Identitas landing page (nama & logo) mengikuti Pengaturan > Fitur &
    // Modul yang diatur admin master -- lihat app/Livewire/Master/Settings.
    // Fallback ke identitas bawaan "SIMS" selama admin belum mengatur apa pun.
    $__brandName = \App\Models\Setting::get('app_name');
    $__brandLogo = \App\Models\Setting::get('app_logo');

    // Konten & show/hide tiap section landing page, diatur admin master
    // lewat Pengaturan > Landing Page -- lihat App\Livewire\Master\Settings\Index
    // (tab "landing") & App\Models\Setting::LANDING_DEFAULTS untuk teks bawaan
    // SELAMA admin belum pernah menyimpan pengaturan ini.
    $__landingDefaults = \App\Models\Setting::LANDING_DEFAULTS;
    $__landingText = fn (string $key) => \App\Models\Setting::get($key, $__landingDefaults[$key]);

    // Hero (halaman utama) -- judul besar paling atas landing page. Section
    // ini selalu tampil (tidak ada toggle enabled/disabled), hanya teksnya
    // yang bisa diubah admin lewat Pengaturan > Landing Page > Hero.
    $__heroTitleTop    = $__landingText('landing_hero_title_top');
    $__heroTitleBottom = $__landingText('landing_hero_title_bottom');
    $__heroScrollText  = $__landingText('landing_hero_scroll_text');
    $__heroTitleTopLines    = explode("\n", $__heroTitleTop);
    $__heroTitleBottomLines = explode("\n", $__heroTitleBottom);

    // Jarak vertikal (rem) judul atas-bawah hero di desktop, diatur lewat
    // Pengaturan > Landing Page > Hero. Dibatasi 0-8 supaya nilai aneh di
    // database tidak merusak layout; bawaan 2 = tampilan asli (my-[2rem]).
    $__heroTitleGap = max(0, min(8, (float) $__landingText('landing_hero_title_gap')));
    $__heroTitleGap = rtrim(rtrim(number_format($__heroTitleGap, 2, '.', ''), '0'), '.');
    $__heroTitleGap = $__heroTitleGap === '' ? '0' : $__heroTitleGap;

    // Lebar antar kurung (rem) & pergeseran elemen ASCII (px) di hero, diatur
    // lewat Pengaturan > Landing Page > Hero. Dibatasi sama seperti validasi
    // form; bawaan (14 / 0 / 0) = tampilan asli sebelum bisa diatur.
    $__heroNum = function (string $key, float $min, float $max): string {
        $n = max($min, min($max, (float) \App\Models\Setting::get($key, \App\Models\Setting::LANDING_DEFAULTS[$key])));
        $n = rtrim(rtrim(number_format($n, 2, '.', ''), '0'), '.');
        return ($n === '' || $n === '-0') ? '0' : $n;
    };
    $__heroBracketWidth = $__heroNum('landing_hero_bracket_width', 4, 40);
    $__heroAsciiX       = $__heroNum('landing_hero_ascii_x', -300, 300);
    $__heroAsciiY       = $__heroNum('landing_hero_ascii_y', -300, 300);

    // Logo/foto custom elemen ASCII di hero -- diatur admin lewat Pengaturan >
    // Landing Page > Hero. Dipakai berupa path relatif (same-origin) supaya
    // canvas di ascii-3d-hero.js tidak terblokir CORS. Kosong = model 3D bawaan.
    $__heroLogo = \App\Models\Setting::get('landing_hero_logo');
    $__heroLogoUrl = $__heroLogo
        ? (parse_url(asset('storage/' . $__heroLogo), PHP_URL_PATH) ?: null)
        : null;

    // Logo/ikon custom loading screen -- diatur admin lewat Pengaturan >
    // Landing Page > Hero. Fallback ke asset bawaan (images/LogoLoading.png)
    // SELAMA admin belum pernah mengunggah logo sendiri.
    $__loaderLogo = \App\Models\Setting::get('landing_loader_logo');
    $__loaderLogoUrl = $__loaderLogo
        ? asset('storage/' . $__loaderLogo)
        : asset('images/LogoLoading.png');

    // Foto custom section "Tentang" -- diatur admin lewat Pengaturan >
    // Landing Page > Tentang. Fallback ke asset bawaan (images/images (1).jpg)
    // SELAMA admin belum pernah mengunggah foto sendiri.
    $__tentangPhoto = \App\Models\Setting::get('landing_tentang_photo');

    $__showFiturSection      = (bool) \App\Models\Setting::get('landing_fitur_enabled', true);
    $__showCaraKerjaSection  = (bool) \App\Models\Setting::get('landing_cara_kerja_enabled', true);
    $__showTentangSection    = (bool) \App\Models\Setting::get('landing_tentang_enabled', true);
    $__showFaqSection        = (bool) \App\Models\Setting::get('landing_faq_enabled', true);

    // Daftar dinamis (Fitur Unggulan, Cara Kerja, FAQ) -- diatur admin master
    // lewat Pengaturan > Landing Page, satu per satu butirnya (tambah, hapus,
    // urutkan). Lihat App\Models\Setting::getList()/LANDING_LIST_DEFAULTS
    // untuk fallback bawaan SELAMA admin belum pernah menyimpan.
    $__fiturItems     = \App\Models\Setting::getList('landing_fitur_items');
    $__caraKerjaItems = \App\Models\Setting::getList('landing_cara_kerja_items');
    $__faqItems       = \App\Models\Setting::getList('landing_faq_items');

    // Pola tata letak menyerong (staggered) untuk tiap kartu fitur, diputar
    // (cycle) lewat modulo supaya jumlah fitur berapa pun tetap tersusun
    // rapi mengikuti gaya desain asli, bukan sekadar berjajar lurus.
    $__fiturLayout = [
        ['ml' => 'lg:ml-[55%]', 'w' => 'max-w-xs', 'dot' => 'bg-slate-950'],
        ['ml' => 'lg:ml-[18%]', 'w' => 'max-w-sm', 'dot' => 'bg-blue-950'],
        ['ml' => 'lg:ml-[42%]', 'w' => 'max-w-sm', 'dot' => 'bg-blue-950'],
        ['ml' => 'lg:ml-[8%]',  'w' => 'max-w-sm', 'dot' => 'bg-blue-950'],
    ];

    // Pola offset vertikal & ukuran kartu "Cara Kerja", diputar per indeks
    // agar scroll horizontal tetap terasa dinamis untuk jumlah langkah apa pun.
    $__caraKerjaLayout = [
        ['y' => 'lg:-translate-y-20', 'w' => 'sm:w-[340px]'],
        ['y' => 'lg:translate-y-20',  'w' => 'sm:w-[340px]'],
        ['y' => 'lg:-translate-y-5',  'w' => 'sm:w-[300px]'],
        ['y' => 'lg:-translate-y-16', 'w' => 'sm:w-[300px]'],
        ['y' => 'lg:translate-y-10',  'w' => 'sm:w-[300px]'],
        ['y' => 'lg:-translate-y-4',  'w' => 'sm:w-[300px]'],
    ];

    // Ikon garis bawaan untuk kartu "Cara Kerja" -- admin memilih salah satu
    // kunci ini lewat <select> di form pengaturan (bukan menulis SVG bebas),
    // supaya tampilan tetap konsisten dengan desain asli. Tiga kunci
    // terakhir (notifikasi, dukungan, waktu) adalah pilihan ikon tambahan
    // untuk variasi -- lihat App\Livewire\Master\Settings\Index::CARA_KERJA_ICONS.
    $__caraKerjaIcons = [
        'akun'      => '<rect x="48" y="30" width="144" height="105" rx="3" stroke="currentColor" stroke-width="1.5"/><circle cx="120" cy="67" r="18" stroke="currentColor" stroke-width="1.5"/><path d="M82 119c8-22 20-32 38-32s30 10 38 32" stroke="currentColor" stroke-width="1.5"/><path d="M18 55h30M18 70h20M192 55h30M202 70h20" stroke="currentColor" stroke-width="1.5"/>',
        'setup'     => '<rect x="72" y="35" width="96" height="110" rx="4" stroke="currentColor" stroke-width="1.5"/><rect x="94" y="52" width="52" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/><path d="M94 78h52M94 94h36M94 110h44" stroke="currentColor" stroke-width="1.5"/><circle cx="120" cy="132" r="5" stroke="currentColor" stroke-width="1.5"/><path d="M25 70h40M175 70h40M25 90h25M190 90h25" stroke="currentColor" stroke-width="1.5"/>',
        'unit'      => '<path d="M50 145V62l70-35 70 35v83H50Z" stroke="currentColor" stroke-width="1.5"/><path d="M82 145V92h76v53M120 27v65" stroke="currentColor" stroke-width="1.5"/><path d="M25 78h25M190 78h25M25 96h25M190 96h25" stroke="currentColor" stroke-width="1.5"/>',
        'transaksi' => '<rect x="35" y="40" width="170" height="105" rx="3" stroke="currentColor" stroke-width="1.5"/><path d="M55 68h130M55 90h75M55 112h105" stroke="currentColor" stroke-width="1.5"/><path d="M25 55h10M205 55h10M25 78h10M205 78h10M25 101h10M205 101h10" stroke="currentColor" stroke-width="1.5"/><circle cx="178" cy="112" r="12" stroke="currentColor" stroke-width="1.5"/><path d="m172 112 4 4 8-9" stroke="currentColor" stroke-width="1.5"/>',
        'laporan'   => '<path d="M42 140V45h156v95H42Z" stroke="currentColor" stroke-width="1.5"/><path d="M65 115V92M95 115V72M125 115V82M155 115V55M185 115V40" stroke="currentColor" stroke-width="7"/><path d="M55 132h130" stroke="currentColor" stroke-width="1.5"/><path d="M25 60h17M198 60h17M25 78h17M198 78h17" stroke="currentColor" stroke-width="1.5"/>',
        'keamanan'  => '<path d="M120 25 184 48v43c0 38-25 57-64 70-39-13-64-32-64-70V48l64-23Z" stroke="currentColor" stroke-width="1.5"/><path d="m91 92 19 19 40-43" stroke="currentColor" stroke-width="1.5"/><path d="M45 65H25M195 65h20M45 82H30M195 82h15" stroke="currentColor" stroke-width="1.5"/>',
        'notifikasi' => '<path d="M120 30c-8 0-14 6-14 14v4c-20 6-32 24-32 50v20l-14 16h120l-14-16v-20c0-26-12-44-32-50v-4c0-8-6-14-14-14Z" stroke="currentColor" stroke-width="1.5"/><path d="M104 148a16 16 0 0 0 32 0" stroke="currentColor" stroke-width="1.5"/><path d="M25 70h20M195 70h20M25 90h15M200 90h15" stroke="currentColor" stroke-width="1.5"/>',
        'dukungan'   => '<path d="M60 100v-10a60 60 0 0 1 120 0v10" stroke="currentColor" stroke-width="1.5"/><rect x="45" y="95" width="25" height="35" rx="6" stroke="currentColor" stroke-width="1.5"/><rect x="170" y="95" width="25" height="35" rx="6" stroke="currentColor" stroke-width="1.5"/><path d="M195 130v8c0 12-10 20-22 20h-15" stroke="currentColor" stroke-width="1.5"/><path d="M25 75h15M200 75h15M25 100h10M205 100h10" stroke="currentColor" stroke-width="1.5"/>',
        'waktu'      => '<circle cx="120" cy="90" r="60" stroke="currentColor" stroke-width="1.5"/><path d="M120 55v35l25 20" stroke="currentColor" stroke-width="1.5"/><path d="M25 90h15M200 90h15M120 25v10M120 145v10" stroke="currentColor" stroke-width="1.5"/>',
    ];
@endphp

@section('title', ($__brandName ?: 'SIMS') . ' - Portal Usaha Mandiri Sekolah')

@section('content')

<main id="main-content" style="opacity: 0; visibility: hidden;">
    {{-- ===================== NAVBAR ===================== --}}
    <nav class="fixed inset-x-0 top-0 z-50 transition-colors duration-300">
        <div class="relative max-w-7xl mx-auto px-6 lg:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center">
                @if ($__brandLogo)
                    {{-- Identitas sekolah (logo & nama diatur admin di menu Pengaturan).
                         Dibungkus pil solid (bukan efek mix-blend seperti logo bawaan)
                         supaya logo unggahan admin -- yang belum tentu monokrom --
                         tetap terbaca jelas di atas latar apa pun saat nav mengambang. --}}
                    <span class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full bg-white/95 dark:bg-slate-900/90 shadow-sm shadow-black/10 backdrop-blur">
                        <span class="h-6 w-6 rounded-full overflow-hidden shrink-0 flex items-center justify-center bg-white">
                            <img src="{{ asset('storage/' . $__brandLogo) }}" alt="{{ $__brandName ?: 'SIMS' }}" class="h-full w-full object-contain">
                        </span>
                        <span class="text-slate-900 dark:text-white text-[13px] font-bold tracking-tight leading-none truncate max-w-[160px]">{{ $__brandName ?: 'SIMS' }}</span>
                    </span>
                @else
                    {{-- Logo untuk mode terang --}}
                    <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto block dark:hidden mix-blend-difference">

                    {{-- Logo untuk mode gelap --}}
                    <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto hidden dark:block mix-blend-difference">
                @endif
            </a>

            {{-- Nav Links dengan indikator "rolling" ala cantor8 (desktop) --}}
            <div id="nav-pill" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden md:flex items-center gap-1 rounded-[2px] bg-black/70 dark:bg-blue-950/70 px-1 py-1">
                <span
                    id="nav-indicator"
                    class="absolute top-1 left-0 h-[calc(93%-0.3rem)] rounded-[1px] bg-white pointer-events-none will-change-transform"
                    style="width:0"
                ></span>
                <a href="#home" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                    <span class="relative block h-4 overflow-hidden">
                        <span class="nav-track flex flex-col will-change-transform">
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Home</span>
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Home</span>
                        </span>
                    </span>
                </a>
                @if ($__showCaraKerjaSection)
                    <a href="#cara-kerja" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                        <span class="relative block h-4 overflow-hidden">
                            <span class="nav-track flex flex-col will-change-transform">
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">Cara Kerja</span>
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">Cara Kerja</span>
                            </span>
                        </span>
                    </a>
                @endif
                @if ($__showTentangSection)
                    <a href="#tentang" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                        <span class="relative block h-4 overflow-hidden">
                            <span class="nav-track flex flex-col will-change-transform">
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">Tentang</span>
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">Tentang</span>
                            </span>
                        </span>
                    </a>
                @endif
                @if ($__showFaqSection)
                    <a href="#faq" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                        <span class="relative block h-4 overflow-hidden">
                            <span class="nav-track flex flex-col will-change-transform">
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">FAQ</span>
                                <span class="leading-4 text-[11px] font-semibold tracking-tight">FAQ</span>
                            </span>
                        </span>
                    </a>
                @endif
            </div>

            {{-- Grup kanan (tablet ke atas) --}}
            <div
                class="hidden sm:flex items-center gap-1 rounded-[2px] bg-black/70 dark:bg-blue-900/70 border border-white/10 p-0.5"
            >
                {{-- Dark/Light Mode Toggle --}}
                <button
                    id="theme-toggle"
                    type="button"
                    aria-label="Ganti tema gelap/terang"
                    class="nav-fade-item flex h-7 w-8 items-center justify-center rounded-[3px] text-white/80 hover:bg-white/10 hover:text-white transition-all duration-300"
                >
                    <svg
                        id="theme-icon-sun"
                        class="hidden h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                    </svg>

                    <svg
                        id="theme-icon-moon"
                        class="h-4 w-4"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                </button>

                {{-- CTA --}}
                <a href="{{ route('login') }}" class="group inline-flex items-center gap-2 rounded-[1px] bg-white pt-[3px] pb-[3px] pl-2 pr-1 text-[11px] font-medium text-blue-900">
                    <span
                        class="login-text relative inline-flex items-center overflow-hidden text-[12px] font-[450] tracking-tight"
                        data-text="Buka Dashboard"
                    ></span>

                    <span class="flex h-6 w-6 items-center justify-center rounded-[3px] bg-blue-900/90">
                        <svg
                            class="h-3 w-3 transition-transform duration-300 group-hover:rotate-45"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="white"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M7 17L17 7M17 7H8M17 7V16"/>
                        </svg>
                    </span>
                </a>
            </div>

            {{-- Tombol Hamburger (mobile only, muncul di bawah breakpoint sm) --}}
            <button
                id="mobile-menu-toggle"
                type="button"
                aria-label="Buka menu navigasi"
                aria-expanded="false"
                aria-controls="mobile-menu"
                class="sm:hidden flex h-9 w-9 items-center justify-center rounded-[3px] bg-black/70 dark:bg-blue-900/70 border border-white/10 text-white/90"
            >
                <svg id="mobile-menu-icon-open" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 6h18M3 12h18M3 18h18"/>
                </svg>
                <svg id="mobile-menu-icon-close" class="hidden h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        {{-- Panel Menu Mobile --}}
        <div
            id="mobile-menu"
            class="sm:hidden hidden flex-col gap-1 mx-4 mt-1 mb-2 rounded-[4px] bg-black/85 dark:bg-blue-950/85 border border-white/10 p-2"
        >
            <a href="#home" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Home</a>
            @if ($__showCaraKerjaSection)
                <a href="#cara-kerja" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Cara Kerja</a>
            @endif
            @if ($__showTentangSection)
                <a href="#tentang" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Tentang</a>
            @endif
            @if ($__showFaqSection)
                <a href="#faq" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">FAQ</a>
            @endif

            <div class="flex items-center justify-between gap-2 mt-1 px-1">
                {{-- Theme toggle versi mobile (id terpisah agar tidak duplikat) --}}
                <button
                    id="theme-toggle-mobile"
                    type="button"
                    aria-label="Ganti tema gelap/terang"
                    class="flex h-9 w-10 items-center justify-center rounded-[3px] bg-white/10 text-white/90"
                >
                    <svg id="theme-icon-sun-mobile" class="hidden h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/>
                        <path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                    </svg>
                    <svg id="theme-icon-moon-mobile" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                    </svg>
                </button>

                {{-- CTA versi mobile, mengikuti pola login-cta di bagian lain halaman --}}
                <a href="{{ route('login') }}" class="login-cta group flex-1 inline-flex items-center justify-center gap-4 rounded-[2px] bg-white py-2 px-3 text-[11px] font-medium text-blue-900">
                    <span
                        class="login-text relative inline-flex items-center overflow-hidden text-[12px] font-semibold tracking-tight"
                        data-text="Buka Dashboard"
                    ></span>
                    <span class="flex h-6 w-6 items-center justify-center rounded-[3px] bg-blue-900/90">
                        <svg class="h-3 w-3 transition-transform duration-300 group-hover:rotate-45" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M7 17L17 7M17 7H8M17 7V16"/>
                        </svg>
                    </span>
                </a>
            </div>
        </div>
    </nav>

    {{-- ===================== HERO SECTION ===================== --}}
    {{-- Judul (baris atas & bawah) dan label scroll indicator diatur admin
         lewat Pengaturan > Landing Page > Hero -- lihat App\Livewire\Master\
         Settings\Index & App\Models\Setting::LANDING_DEFAULTS. Section ini
         selalu tampil (tidak ada toggle enabled/disabled) karena menjadi
         halaman pembuka; hanya teksnya yang bisa diubah. --}}
    <section id="home" class="relative overflow-hidden bg-gradient-to-b from-blue-50/60 to-slate-50 dark:from-slate-900 dark:to-slate-950 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-6 min-h-screen flex flex-col justify-center pb-10">

            <!-- Parent utama dilepas class relative-nya agar text tepi bisa merapat ke ujung layar -->
            <div data-animate="hero-text" class="pt-10 lg:pt-6 z-10 flex flex-col items-center self-center w-full">             
                
                <!-- PERBAIKAN: h1 sekarang menjadi 'relative' dan ditambahkan 'w-full' -->
                <h1 class="relative w-full max-w-[680px] font-display text-4xl lg:text-[4rem] tracking-tighter font-medium text-slate-900 dark:text-white flex flex-col items-center text-center">                 
                    
                    <!-- ========================================================= -->

                    @foreach ($__heroTitleTopLines as $__i => $__line)
                        <span class="block{{ $__i === count($__heroTitleTopLines) - 1 ? ' mb-1' : '' }}">{{ $__line }}</span>
                    @endforeach

                    {{-- Kurung buka-tutup: dekorasi khusus desktop, disembunyikan total di mobile
                        (hidden, bukan cuma di-scale/shrink) supaya heading mobile terasa
                        seolah elemen ini memang tidak pernah ada di layout.
                        Diberi "relative" agar jadi anchor positioning untuk ASCII 3D
                        di dalamnya — sehingga posisi ASCII selalu mengikuti kurung ini
                        (yang sudah otomatis center via mx-auto), bukan lagi terikat
                        offset pixel manual terhadap section. --}}
                    <span class="hidden lg:flex relative justify-between w-full mx-auto leading-none" style="max-width: {{ $__heroBracketWidth }}rem; margin-top: {{ $__heroTitleGap }}rem; margin-bottom: {{ $__heroTitleGap }}rem;">
                        <span>(</span>
                        <div
                            id="ascii-3d-container"
                            data-animate="hero-visual"
                            @if ($__heroLogoUrl) data-ascii-image="{{ $__heroLogoUrl }}" @endif
                            class="pointer-events-none absolute w-[180px] h-[180px] text-blue-900 dark:text-slate-200"
                            {{-- Posisi: titik tengah kurung (50%/50%) dikurangi setengah ukuran elemen (90px),
                                 +12px = offset horizontal bawaan desain asli, lalu ditambah geseran admin.
                                 Memakai margin (bukan transform) karena transform dipakai animasi GSAP. --}}
                            style="left: 50%; top: 50%; margin-left: calc(-78px + {{ $__heroAsciiX }}px); margin-top: calc(-90px + {{ $__heroAsciiY }}px);"
                        ></div>

                        <span>)</span>
                    </span>
                    
                    @foreach ($__heroTitleBottomLines as $__i => $__line)
                        <span class="block{{ $__i === 0 ? ' mt-1' : '' }}">{{ $__line }}</span>
                    @endforeach
                </h1>
                
            </div>

        </div>

        {{-- Scroll down indicator --}}
        <div
            data-animate="hero-text"
            class="absolute bottom-6 right-6 lg:bottom-10 lg:right-10 flex items-center gap-2 text-slate-500 dark:text-slate-400"
        >
            <span class="text-[11px] font-medium tracking-wide uppercase">{{ $__heroScrollText }}</span>
            <svg
                id="scroll-down-icon"
                class="h-3.5 w-3.5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M12 5v14M5 12l7 7 7-7"/>
            </svg>
        </div>
    </section>

    {{-- ===================== TRUSTED BY SECTION ===================== --}}
    @php
        // Bagian ini menampilkan unit usaha AKTIF milik sekolah (bukan logo
        // mitra eksternal statis), supaya selalu sinkron dengan data yang
        // dikelola admin di menu Unit Usaha -- tidak ada data ganda yang
        // harus diperbarui manual di dua tempat. Bisa dimatikan admin lewat
        // toggle "Tampilkan Unit Usaha di Landing Page" di menu Pengaturan.
        $__showUnitsSection = (bool) \App\Models\Setting::get('show_units_on_landing', true);

        // "selected": admin memilih satu per satu unit usaha yang tampil
        // (lihat landingSelectedUnitIds di Pengaturan > Landing Page).
        // Selain itu ("all", termasuk saat admin belum pernah mengatur ini),
        // seluruh unit usaha berstatus aktif tampil otomatis -- perilaku lama.
        $__unitsMode = \App\Models\Setting::get('landing_units_mode', 'all');
        $__selectedUnitIds = json_decode(\App\Models\Setting::get('landing_selected_unit_ids', '[]'), true) ?: [];

        $__landingUnits = $__showUnitsSection
            ? \App\Models\Unit::where('is_active', true)
                ->when($__unitsMode === 'selected', fn ($q) => $q->whereIn('id', $__selectedUnitIds))
                ->orderBy('name')
                ->get()
            : collect();
    @endphp
    @if ($__showUnitsSection && $__landingUnits->isNotEmpty())
    <section class="py-32 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
        <div class="max-w-[100vw]mx-auto px-6 lg:px-8 text-center">

            <h2 data-reveal-text data-animate="bento" class="mt-20 font-display text-3xl lg:text-5xl font-medium text-blue-950 leading-none tracking-tighter dark:text-white">
                {!! nl2br(e($__landingText('landing_mitra_title'))) !!}
            </h2>

            <p data-reveal-text data-animate="bento" class="mt-5 text-blue-950/70 dark:text-white/70 max-w-md mx-auto font-semibold leading-tight tracking-tight">
                {{ $__landingText('landing_mitra_description') }}
            </p>

            <div class="mt-32 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

                {{-- Tile pertama: identitas sekolah (logo/nama dari Pengaturan) --}}
                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    @if ($__brandLogo)
                        <img
                            src="{{ asset('storage/' . $__brandLogo) }}"
                            alt="Logo {{ $__brandName ?: 'Sekolah' }}"
                            class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                        >
                    @else
                        <img
                            src="{{ asset('LogoMitra/LogoSMK.png') }}"
                            alt="Logo SMK"
                            class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                        >
                    @endif
                </div>

                {{-- Tile berikutnya: setiap Unit Usaha yang sedang aktif --}}
                @foreach ($__landingUnits as $unit)
                    <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                        @if ($unit->logo)
                            <img
                                src="{{ asset('storage/' . $unit->logo) }}"
                                alt="Logo {{ $unit->name }}"
                                class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                            >
                        @else
                            {{-- Fallback: inisial nama unit, selaras dengan kartu Unit Usaha di dashboard --}}
                            <span class="text-white/60 text-2xl font-bold tracking-tight" title="{{ $unit->name }}">
                                {{ strtoupper(substr($unit->name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                @endforeach

            </div>
        </div>
    </section>
    @endif

    {{-- ===================== FITUR UNGGULAN ===================== --}}
    @if ($__showFiturSection)
    <section class="relative overflow-hidden dark:bg-slate-950 py-24 lg:py-50 px-6 lg:px-8 transition-colors duration-300">

        <div class="relative max-w-7xl mx-auto">


            <div class="flex justify-start gap-2 flex-col">
                <div class="flex justify-start gap-2 items-center">
                    <span class="h-1 w-1 mt-2 ml-1 shrink-0 bg-slate-950 dark:bg-white blink-dot"></span>
                    <p data-reveal-text class="mt-3 text-sm lg:text-[12px] font-bold uppercase tracking-tight text-blue-950/70 dark:text-white">
                        {{ $__landingText('landing_fitur_eyebrow') }}
                    </p>
                </div>
                <h2 data-reveal-text class="font-display text-4xl sm:text-5xl lg:text-4xl font-semibold leading-none tracking-tighter text-blue-950 max-w-3xl dark:text-white">
                    {!! nl2br(e($__landingText('landing_fitur_title'))) !!}
                </h2>
            </div>
            {{-- Heading besar --}}


            {{-- List fitur, tersusun menyerong (staggered) — jumlah & isi
                 diatur admin lewat Pengaturan > Landing Page > Fitur Unggulan. --}}
            <div class="mt-24 lg:mt-52 flex flex-col gap-20 lg:gap-48">
                @foreach ($__fiturItems as $__fiturItem)
                    @php $__layout = $__fiturLayout[$loop->index % count($__fiturLayout)]; @endphp
                    <div class="{{ $__layout['ml'] }} {{ $__layout['w'] }} flex items-start gap-3" data-animate="fitur-item">
                        <span class="mt-2.5 h-1.5 w-1.5 shrink-0 {{ $__layout['dot'] }} dark:bg-white blink-dot"></span>
                        <div>
                            <h3 data-reveal-text class="font-display text-2xl lg:text-3xl font-medium tracking-tighter text-blue-950 dark:text-white">
                                {{ $__fiturItem['title'] ?? '' }}
                            </h3>
                            <p data-reveal-text class="mt-3 text-sm lg:text-sm font-semibold leading-tight tracking-tight text-blue-950/70 dark:text-white/70">
                                {{ $__fiturItem['description'] ?? '' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Mitra → How  --}}
    <div
        class="pixel-divider relative grid w-full overflow-hidden"
        data-section-light="#172554"
        data-section-dark="rgb(15, 23, 42)"
        data-accent-light="rgb(37, 91, 157)"
        data-accent-dark="rgb(52, 64, 82)"
        data-prev-light="#f8fafc"
        data-prev-dark="rgb(2, 6, 23)"
    ></div>

    {{-- ===================== CARA KERJA (Sticky Horizontal Scroll) ===================== --}}
    @if ($__showCaraKerjaSection)
    <section id="cara-kerja" class="relative bg-blue-950 dark:bg-slate-900 transition-colors duration-300 pt-30">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 mb-40">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

                {{-- Heading kiri --}}
                <h2 data-reveal-text class="font-medium tracking-tighter leading-none self-start text-4xl lg:text-6xl font-bold text-white lg:max-w-sm">
                    {{ $__landingText('landing_cara_kerja_title') }}
                </h2>

                {{-- Paragraf + tombol kanan --}}
                <div class="flex flex-col items-start gap-4 lg:items-start lg:max-w-sm">
                    <p data-reveal-text class="text-white/80 text-left text-sm font-semibold tracking-tight leading-tight">
                        {{ $__landingText('landing_cara_kerja_description') }}
                    </p>
                    <a            
                        href="/login"
                        class="login-cta group mt-3 inline-flex items-center gap-6 rounded-[2px] bg-blue-900 pt-1 pb-1 pl-3 pr-1 text-[11px] font-medium text-white"
                    >
                        <span
                            class="login-text relative inline-flex items-center overflow-hidden text-[13px] font-semibold tracking-tight"
                            data-text="Buka Dashboard"
                        ></span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white/90">
                            <svg
                                class="h-5 w-5 transition-transform duration-300 group-hover:rotate-45"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="#1E3A8A"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M7 17L17 7M17 7H8M17 7V16"/>
                            </svg>
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <div id="horizontal-wrapper" class="relative mt-16 h-[560px] lg:h-screen overflow-x-auto lg:overflow-hidden">
            <div id="horizontal-track" class="flex h-full items-center gap-18 px-6 lg:px-8 snap-x snap-mandatory will-change-transform">

                {{-- Kartu langkah "Cara Kerja" — jumlah, badge, ikon, judul,
                     dan deskripsi tiap kartu diatur admin lewat Pengaturan >
                     Landing Page > Cara Kerja. Offset vertikal & lebar kartu
                     diputar (cycle) lewat $__caraKerjaLayout supaya tampilan
                     tetap dinamis untuk jumlah langkah berapa pun. --}}
                @foreach ($__caraKerjaItems as $__step)
                    @php
                        $__stepLayout = $__caraKerjaLayout[$loop->index % count($__caraKerjaLayout)];
                        $__stepIconKey = $__step['icon'] ?? 'unit';
                        $__stepIcon = $__caraKerjaIcons[$__stepIconKey] ?? $__caraKerjaIcons['unit'];
                    @endphp
                    <div class="howitworks-card snap-center shrink-0 w-[85vw] {{ $__stepLayout['w'] }} h-[75%] lg:h-[65%] {{ $__stepLayout['y'] }} rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                        {{-- Header --}}
                        <div class="relative z-10 flex items-start justify-between">
                            <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                                {{ $__step['badge'] ?? '' }}
                            </span>

                            <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                            </span>
                        </div>

                        {{-- Visual --}}
                        <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                            <svg class="w-[78%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                                {!! $__stepIcon !!}
                            </svg>
                        </div>

                        {{-- Content --}}
                        <div class="relative z-10 mt-auto max-w-[95%]">
                            <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                                {{ $__step['title'] ?? '' }}
                            </h3>

                            <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                                {{ $__step['description'] ?? '' }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </section>
    @endif

    {{-- ===================== ABOUT ===================== --}}
    @if ($__showTentangSection)
    <section id="tentang" class="relative bg-blue-950 dark:bg-slate-900 p-6 transition-colors duration-300 overflow-hidden">
        <div class="flex flex-col lg:flex-row min-h-[600px] lg:min-h-screen my-30">

            {{-- Kiri: Gambar (60%) -- diatur admin lewat Pengaturan > Landing
                 Page > Tentang. Fallback ke asset bawaan SELAMA admin belum
                 pernah mengunggah foto sendiri. --}}
            <div class="relative w-full lg:w-[60%] h-72 lg:h-[100vh] bg-slate-800 flex-shrink-0 overflow-hidden">
                <img
                    src="{{ $__tentangPhoto ? asset('storage/' . $__tentangPhoto) : asset('images/images (1).jpg') }}"
                    alt="About"
                    class="absolute inset-0 w-full h-full object-cover opacity-50 hover:opacity-100 transition-opacity duration-300"
                >
            </div>

            {{-- Kanan: Konten (40%) --}}
            <div class="relative flex flex-col justify-between w-full lg:w-[40%] px-4 py-16 lg:pl-6 lg:pr-16 lg:py-6">

                {{-- Heading + deskripsi --}}
                <div>
                    <h2 data-reveal-text class="font-display text-2xl lg:text-4xl font-medium text-white leading-tighter tracking-tighter">
                        {{ $__landingText('landing_tentang_title') }}
                    </h2>
                </div>

                {{-- Deskripsi + tombol --}}
                <div class="mt-12 lg:mt-0 flex flex-col">
                    <p data-reveal-text class="text-white text-xs font-semibold leading-tight tracking-tight">
                        {{ $__landingText('landing_tentang_description') }}
                    </p>

                    {{-- Tombol --}}
                    <div>
                        <a            
                            href="/login"
                            class="login-cta group mt-3 inline-flex items-center gap-6 rounded-[2px] bg-blue-900 pt-1 pb-1 pl-3 pr-1 text-[11px] font-medium text-white"
                        >
                            <span
                                class="login-text relative inline-flex items-center overflow-hidden text-[13px] font-semibold tracking-tight"
                                data-text="Buka Dashboard"
                            ></span>

                            <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white/90">
                                <svg
                                    class="h-5 w-5 transition-transform duration-300 group-hover:rotate-45"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="#1E3A8A"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M7 17L17 7M17 7H8M17 7V16"/>
                                </svg>
                            </span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
    @endif

    {{-- Contoh instance lain, misal Bento → FAQ --}}
    <div
        class="pixel-divider relative grid w-full overflow-hidden"
        data-section-light="#f8fafc"
        data-section-dark="rgb(2, 6, 23)"
        data-accent-light="rgb(230, 241, 255)"
        data-accent-dark="rgb(52, 64, 82)"
        data-prev-light="#172554"
        data-prev-dark="rgb(15, 23, 42)""
    ></div>

    {{-- ===================== FAQ ===================== --}}
    @if ($__showFaqSection)
    <section id="faq" class="py-60 px-6 lg:px-6 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
        <div class="max-w-[100vw] mx-auto">

            {{-- Header: label kiri + heading besar kanan --}}
            <div class="grid grid-cols-1 lg:grid-cols-[440px_1fr] gap-4 lg:gap-10 items-start">
                <span data-reveal-text class="text-xs font-semibold uppercase tracking-tight text-blue-900 dark:text-slate-400">
                    FAQ
                </span>
                <div class="flex justicy-start gap-3">
                    <span class="blink-dot mt-2.5 h-2 w-2 shrink-0 bg-blue-950"></span>
                    <h2 data-reveal-text class="font-display text-3xl lg:text-5xl font-medium leading-tighter tracking-tighter text-blue-900 dark:text-white">
                        {{ $__landingText('landing_faq_title') }}
                    </h2>
                </div>
            </div>

            {{-- Bawah: label kiri + accordion kanan --}}
            <div class="grid grid-cols-1 lg:grid-cols-[440px_1fr] gap-4 lg:gap-10 items-start pt-8">
                <span data-reveal-text class="text-sm font-semibold pt-7 text-blue-900 dark:text-white">
                    Pertanyaan Umum
                </span>

                <div class="faq-list flex flex-col">

                    @foreach ($__faqItems as $__faq)
                        <div class="faq-item border-b border-slate-200 dark:border-slate-800">
                            <button
                                type="button"
                                class="faq-trigger group flex w-full items-center justify-between py-6 text-left"
                            >
                                <span data-reveal-text class="text-base lg:text-base tracking-tighter font-semibold text-blue-900 dark:text-white">
                                    {{ $__faq['question'] ?? '' }}
                                </span>

                                <span class="faq-icon relative flex h-6 w-6 shrink-0 items-center justify-center text-blue-900 dark:text-white">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h14"/>
                                        <path d="M12 5v14"/>
                                    </svg>
                                </span>
                            </button>

                            <div class="faq-panel grid grid-rows-[0fr] transition-[grid-template-rows] duration-600 ease-out">
                                <div class="overflow-hidden">
                                    <p class="pb-6 text-sm lg:text-sm tracking-tight text-blue-900/70 dark:text-slate-400 max-w-2xl">
                                        {{ $__faq['answer'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    </section>
    @endif

    {{-- ===================== FOOTER ===================== --}}
    <footer class="bg-slate-950 dark:bg-slate-900 pt-20 pb-5 px-6 lg:px-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-12 lg:gap-28 pb-40">

                {{-- Kiri: Heading + Subscribe Form --}}
                <div id="kontak">
                    <h2 class="font-display text-3xl font-medium leading-none tracking-tighter text-white">
                        {!! nl2br(e($__landingText('landing_footer_title'))) !!}
                    </h2>

                    <form class="mt-6 flex flex-col sm:flex-row gap-2 max-w-md">
                        <input
                            type="email"
                            placeholder="Alamat E-mail"
                            class="flex-1 rounded-[1px] border border-white/15 bg-transparent px-4 py-2 text-xs text-white placeholder:text-slate-500 focus:outline-none focus:border-white/40 transition-colors"
                        />
                        <button
                            type="submit"
                            class="rounded-[1px] bg-blue-900 hover:bg-blue-800 px-6 py-2 text-xs font-medium tracking-tighter text-white transition-colors"
                        >
                            Hubungi
                        </button>
                    </form>
                </div>

                {{-- Kanan: dua baris, masing-masing 2 kolom link --}}
                <div class="flex flex-col gap-24">

                    {{-- Baris atas: Sistem & Fitur --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-tighter text-white/50 mb-4">
                                Sistem
                            </p>
                            <ul class="space-y-1 text-[12px] font-medium tracking-tighter">
                                <li><a href="#home" class="text-white/90 hover:text-white transition-colors">Beranda</a></li>
                                <li><a href="#tentang" class="text-white/90 hover:text-white transition-colors">Tentang</a></li>
                                <li><a href="#cara-kerja" class="text-white/90 hover:text-white transition-colors">Cara Kerja</a></li>
                                <li><a href="{{ route('login') }}" class="text-white/90 hover:text-white transition-colors">Buka Dashboard</a></li>
                                <li><a href="#faq" class="text-white/90 hover:text-white transition-colors">FAQ</a></li>
                            </ul>
                        </div>

                        <div>
                            <p class="text-xs font-semibold uppercase tracking-tighter text-white/50 mb-4">
                                Fitur
                            </p>
                            <ul class="space-y-1 text-[12px] font-medium tracking-tighter">
                                <li><a href="#fitur-transaksi" class="text-white/90 hover:text-white transition-colors">Pencatatan Transaksi</a></li>
                                <li><a href="#fitur-laporan" class="text-white/90 hover:text-white transition-colors">Laporan Keuangan</a></li>
                                <li><a href="#fitur" class="text-white/90 hover:text-white transition-colors">Manajemen Unit Usaha</a></li>
                                <li><a href="#fitur-multiadmin" class="text-white/90 hover:text-white transition-colors">Multi Admin</a></li>
                                <li><a href="#fitur-keamanan" class="text-white/90 hover:text-white transition-colors">Keamanan Data</a></li>
                            </ul>
                        </div>
                    </div>

                    {{-- Baris bawah: Bantuan & Kontak --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-tighter text-white/50 mb-4">
                                Bantuan
                            </p>
                            <ul class="space-y-1 text-[12px] font-medium tracking-tighter">
                                <li><a href="#" class="text-white/90 hover:text-white transition-colors">Panduan Penggunaan</a></li>
                                <li><a href="#kontak" class="text-white/90 hover:text-white transition-colors">Hubungi Admin</a></li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

            {{-- Bottom bar --}}
            <div class="mt-16 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs tracking-tighter font-semibold text-white/30">&copy; {{ date('Y') }}.</p>
                <p class="text-xs tracking-tighter font-semibold text-white/30">Portal Internal Sekolah. Seluruh hak dilindungi.</p>
            </div>
        </div>
    </footer>

</main>

<!-- Loading Screen Layer (K95 Style Dual Wipe Transition) -->
<div id="page-loader" class="fixed inset-0 z-[999999] overflow-hidden pointer-events-auto bg-[#0a1128]">
    <!-- Layer Utama: Logo + Counter [0] (z-10) -->
    <div id="loader-screen" class="absolute inset-0 z-10 flex items-center justify-center bg-[#0a1128]">
        <div class="relative flex items-center justify-center">
            <img 
                src="{{ $__loaderLogoUrl }}" 
                alt="Symbol" 
                class="w-16 h-16 object-contain"
            />
            <span 
                id="loader-counter" 
                class="absolute -right-[5px] -top-2 translate-x-full font-mono tracking-tight text-sm tabular-nums text-white/90 md:text-base"
                aria-live="polite"
            >
                [0]
            </span>
        </div>
    </div>

    <!-- Tirai 1: Gold / Aksen (z-20) -->
    <div 
        id="loader-curtain-1" 
        class="absolute inset-0 z-20 bg-blue-950 pointer-events-none translate-y-full"
    ></div>

    <!-- Tirai 2: Hitam / Utama (z-30) -->
    <div 
        id="loader-curtain-2" 
        class="absolute inset-0 z-30 bg-white pointer-events-none translate-y-full"
    ></div>
</div>

<script>
    (function () {
        const toggleBtn   = document.getElementById('mobile-menu-toggle');
        const mobileMenu  = document.getElementById('mobile-menu');
        const iconOpen    = document.getElementById('mobile-menu-icon-open');
        const iconClose   = document.getElementById('mobile-menu-icon-close');
        const mobileLinks = document.querySelectorAll('.mobile-nav-link');

        if (!toggleBtn || !mobileMenu) return;

        function closeMenu() {
            mobileMenu.classList.add('hidden');
            mobileMenu.classList.remove('flex');
            iconOpen.classList.remove('hidden');
            iconClose.classList.add('hidden');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }

        function openMenu() {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('flex');
            iconOpen.classList.add('hidden');
            iconClose.classList.remove('hidden');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }

        toggleBtn.addEventListener('click', function () {
            const isOpen = !mobileMenu.classList.contains('hidden');
            isOpen ? closeMenu() : openMenu();
        });

        mobileLinks.forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 768) closeMenu();
        });
    })();
</script>

@endsection