@extends('layouts.landing')

@section('title', 'SIMS - Portal Usaha Mandiri Sekolah')

@section('content')

<main id="main-content" style="opacity: 0; visibility: hidden;">
    {{-- ===================== NAVBAR ===================== --}}
    <nav class="fixed inset-x-0 top-0 z-50 transition-colors duration-300">
        <div class="relative max-w-7xl mx-auto px-6 lg:px-6 h-16 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="flex items-center">
                {{-- Logo untuk mode terang --}}
                <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto block dark:hidden mix-blend-difference">

                {{-- Logo untuk mode gelap --}}
                <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto hidden dark:block mix-blend-difference">
            </a>

            {{-- Nav Links dengan indikator "rolling" ala cantor8 (desktop) --}}
            <div id="nav-pill" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 hidden md:flex items-center gap-1 rounded-[2px] bg-black/70 dark:bg-blue-950/70 backdrop-blur px-1 py-1">
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
                <a href="#cara-kerja" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                    <span class="relative block h-4 overflow-hidden">
                        <span class="nav-track flex flex-col will-change-transform">
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Cara Kerja</span>
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Cara Kerja</span>
                        </span>
                    </span>
                </a>
                <a href="#tentang" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                    <span class="relative block h-4 overflow-hidden">
                        <span class="nav-track flex flex-col will-change-transform">
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Tentang</span>
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">Tentang</span>
                        </span>
                    </span>
                </a>
                <a href="#faq" class="nav-link nav-fade-item relative z-10 px-4 py-1.5">
                    <span class="relative block h-4 overflow-hidden">
                        <span class="nav-track flex flex-col will-change-transform">
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">FAQ</span>
                            <span class="leading-4 text-[11px] font-semibold tracking-tight">FAQ</span>
                        </span>
                    </span>
                </a>
            </div>

            {{-- Grup kanan (tablet ke atas) --}}
            <div
                class="hidden sm:flex items-center gap-1 rounded-[2px] bg-black/70 dark:bg-blue-900/70 backdrop-blur border border-white/10 p-0.5"
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
                class="sm:hidden flex h-9 w-9 items-center justify-center rounded-[3px] bg-black/70 dark:bg-blue-900/70 backdrop-blur border border-white/10 text-white/90"
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
            class="sm:hidden hidden flex-col gap-1 mx-4 mt-1 mb-2 rounded-[4px] bg-black/85 dark:bg-blue-950/85 backdrop-blur border border-white/10 p-2"
        >
            <a href="#home" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Home</a>
            <a href="#cara-kerja" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Cara Kerja</a>
            <a href="#tentang" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">Tentang</a>
            <a href="#faq" class="mobile-nav-link px-4 py-2 rounded-[2px] text-[13px] font-semibold tracking-tight text-white/90 hover:bg-white/10">FAQ</a>

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
    <section id="home" class="relative overflow-hidden bg-gradient-to-b from-blue-50/60 to-slate-50 dark:from-slate-900 dark:to-slate-950 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-6 lg:px-6 min-h-screen flex flex-col justify-center pb-10">

            <!-- Parent utama dilepas class relative-nya agar text tepi bisa merapat ke ujung layar -->
            <div data-animate="hero-text" class="pt-10 lg:pt-6 z-10 flex flex-col items-center self-center w-full">             
                
                <!-- PERBAIKAN: h1 sekarang menjadi 'relative' dan ditambahkan 'w-full' -->
                <h1 class="relative w-full max-w-[680px] font-display text-4xl lg:text-[4rem] tracking-tighter font-medium text-slate-900 dark:text-white flex flex-col items-center text-center">                 
                    
                    <!-- ========================================================= -->

                    <span class="block">Kelola Unit</span>
                    <span class="block mb-1">Usaha Sekolah</span>
                    
                    {{-- Kurung buka-tutup: dekorasi khusus desktop, disembunyikan total di mobile
                        (hidden, bukan cuma di-scale/shrink) supaya heading mobile terasa
                        seolah elemen ini memang tidak pernah ada di layout.
                        Diberi "relative" agar jadi anchor positioning untuk ASCII 3D
                        di dalamnya — sehingga posisi ASCII selalu mengikuti kurung ini
                        (yang sudah otomatis center via mx-auto), bukan lagi terikat
                        offset pixel manual terhadap section. --}}
                    <span class="hidden lg:flex relative justify-between w-full max-w-[14rem] mx-auto my-[2rem] leading-none">
                        <span>(</span>
                        <div
                            id="ascii-3d-container"
                            data-animate="hero-visual"
                            class="pointer-events-none absolute inset-0 left-[24px] m-auto w-[180px] h-[180px] text-blue-900 dark:text-slate-200"
                        ></div>

                        <span>)</span>
                    </span>
                    
                    <span class="block mt-1">dalam Satu</span>
                    <span class="block">Portal.</span>             
                </h1>
                
            </div>

        </div>

        {{-- Scroll down indicator --}}
        <div
            data-animate="hero-text"
            class="absolute bottom-6 right-6 lg:bottom-10 lg:right-10 flex items-center gap-2 text-slate-500 dark:text-slate-400"
        >
            <span class="text-[11px] font-medium tracking-wide uppercase">SCROLL</span>
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
    <section class="py-32 bg-slate-50 dark:bg-slate-950 transition-colors duration-300">
        <div class="max-w-[100vw]mx-auto px-6 lg:px-8 text-center">

            <h2 data-reveal-text data-animate="bento" class="mt-20 font-display text-3xl lg:text-5xl font-medium text-blue-950 leading-none tracking-tighter dark:text-white">
                Dipercaya oleh <br class="hidden sm:block" /> Mitra Unit Usaha Sekolah.
            </h2>

            <p data-reveal-text data-animate="bento" class="mt-5 text-blue-950/70 dark:text-white/70 max-w-md mx-auto font-semibold leading-tight tracking-tight">
                Kolaborasi kami tidak berhenti di sistem. Kami bekerja bersama unit usaha, penyedia
                layanan, dan mitra sekolah untuk memastikan setiap transaksi tercatat rapi dan
                dapat dipertanggungjawabkan.
            </p>

            <div class="mt-32 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/LogoSMK.png') }}" 
                        alt="Logo SMK"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/TEFA.png') }}" 
                        alt="Logo TEFA"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/TehSiswa.png') }}" 
                        alt="Logo Teh Siswa"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/ToBengkel.png') }}" 
                        alt="Logo Bengkel"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/Prnt.png') }}" 
                        alt="Logo Foto Copy"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

                <div data-animate="bento" class="aspect-[16/10] rounded-[3px] bg-blue-800 dark:bg-blue-950 border border-white/10 flex items-center justify-center overflow-hidden">
                    <img 
                        src="{{ asset('LogoMitra/BC.png') }}" 
                        alt="Logo Business Center"
                        class="w-20 h-20 object-contain opacity-50 hover:opacity-100 transition-opacity duration-300"
                    >
                </div>

            </div>
        </div>
    </section>

    {{-- ===================== FITUR UNGGULAN ===================== --}}
    <section class="relative overflow-hidden dark:bg-slate-950 py-24 lg:py-50 px-6 lg:px-8 transition-colors duration-300">

        <div class="relative max-w-7xl mx-auto">


            <div class="flex justify-start gap-2 flex-col">
                <div class="flex justify-start gap-2 items-center">
                    <span class="h-1 w-1 mt-2 ml-1 shrink-0 bg-slate-950 dark:bg-white blink-dot"></span>
                    <p data-reveal-text class="mt-3 text-sm lg:text-[12px] font-bold uppercase tracking-tight text-blue-950/70 dark:text-white">
                        Fitur
                    </p>
                </div>
                <h2 data-reveal-text class="font-display text-4xl sm:text-5xl lg:text-4xl font-semibold leading-none tracking-tighter text-blue-950 max-w-3xl dark:text-white">
                    Di Garis Depan <br />
                    Pengelolaan Usaha Sekolah.
                </h2>
            </div>
            {{-- Heading besar --}}


            {{-- List fitur, tersusun menyerong (staggered) --}}
            <div class="mt-24 lg:mt-52 flex flex-col gap-20 lg:gap-48">

                <div class="lg:ml-[55%] max-w-xs flex items-start gap-3" data-animate="fitur-item">
                    <span class="mt-2.5 h-1.5 w-1.5 shrink-0 bg-slate-950 dark:bg-white blink-dot"></span>
                    <div>
                        <h3 data-reveal-text class="font-display text-2xl lg:text-3xl font-medium tracking-tighter text-blue-950  dark:text-white">
                            Transaksi &amp; Inventaris Terpadu
                        </h3>
                        <p data-reveal-text class="mt-3 text-sm lg:text-sm font-semibold leading-tight tracking-tight text-blue-950/70 dark:text-white/70">
                            Catat transaksi harian, transaksi berulang, pembelian ke vendor, hingga stok inventaris dalam satu sistem yang saling terhubung — setiap unit usaha, baik ritel maupun jasa, punya alur kerja yang sesuai kebutuhannya.
                        </p>
                    </div>
                </div>

                <div class="lg:ml-[18%] max-w-sm flex items-start gap-3" data-animate="fitur-item">
                    <span class="mt-2.5 h-1.5 w-1.5 shrink-0 bg-blue-950 dark:bg-white blink-dot"></span>
                    <div>
                        <h3 data-reveal-text class="font-display text-2xl lg:text-3xl font-medium tracking-tighter text-blue-950  dark:text-white">
                            Statistik &amp; Dokumen Resmi Lintas Unit
                        </h3>
                        <p data-reveal-text class="mt-3 text-sm lg:text-sm font-semibold leading-tight tracking-tight text-blue-950/70  dark:text-white/70">
                            Pantau performa seluruh unit usaha lewat statistik dan analitik terpusat, lalu terbitkan dokumen resmi maupun ekspor data kapan saja tanpa perlu merekap manual satu per satu.
                        </p>
                    </div>
                </div>

                <div class="lg:ml-[42%] max-w-sm flex items-start gap-3" data-animate="fitur-item">
                    <span class="mt-2.5 h-1.5 w-1.5 shrink-0 bg-blue-950 blink-dot dark:bg-white"></span>
                    <div>
                        <h3 data-reveal-text class="font-display text-2xl lg:text-3xl font-medium tracking-tighter text-blue-950 dark:text-white">
                            Multi Admin &amp; Hak Akses
                        </h3>
                        <p data-reveal-text class="mt-3 text-sm lg:text-sm font-semibold leading-tight tracking-tight text-blue-950/70 dark:text-white/70">
                            Kelola peran Master Admin dan Admin Unit dengan hak akses yang jelas untuk tiap unit usaha, menjaga keamanan data sekaligus memudahkan pembagian tanggung jawab operasional.
                        </p>
                    </div>
                </div>

                <div class="lg:ml-[8%] max-w-sm flex items-start gap-3" data-animate="fitur-item">
                    <span class="mt-2.5 h-1.5 w-1.5 shrink-0 bg-blue-950 dark:bg-white blink-dot"></span>
                    <div>
                        <h3 data-reveal-text class="font-display text-2xl lg:text-3xl font-medium tracking-tighter text-blue-950 dark:text-white">
                            Audit Log &amp; Aktivitas
                        </h3>
                        <p data-reveal-text class="mt-3 text-sm lg:text-sm font-semibold leading-tight tracking-tight text-blue-950/70 dark:text-white/70">
                            Setiap aktivitas dan perubahan data tercatat rapi dalam audit log, sehingga jejak penggunaan sistem tetap terpantau dan informasi sekolah tetap aman.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
    <section id="cara-kerja" class="relative bg-blue-950 dark:bg-slate-900 transition-colors duration-300 pt-30">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 mb-40">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

                {{-- Heading kiri --}}
                <h2 data-reveal-text class="font-medium tracking-tighter leading-none self-start text-4xl lg:text-6xl font-bold text-white lg:max-w-sm">
                    Mulai Kelola Cerdas.
                </h2>

                {{-- Paragraf + tombol kanan --}}
                <div class="flex flex-col items-start gap-4 lg:items-start lg:max-w-sm">
                    <p data-reveal-text class="text-white/80 text-left text-sm font-semibold tracking-tight leading-tight">
                        6 langkah untuk mulai mengelola unit usaha sekolah. Geser atau scroll untuk melihat tiap langkah.
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

                {{-- Step 1 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[340px] h-[75%] lg:h-[65%] lg:-translate-y-20 rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    {{-- Header --}}
                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            AKUN
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    {{-- Visual --}}
                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[75%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <rect x="48" y="30" width="144" height="105" rx="3" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="120" cy="67" r="18" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M82 119c8-22 20-32 38-32s30 10 38 32" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M18 55h30M18 70h20M192 55h30M202 70h20" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    {{-- Content --}}
                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Akun Dibuatkan Master Admin
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Master Admin membuat akun Admin Unit dan kredensial login dikirim otomatis lewat WhatsApp.
                        </p>
                    </div>
                </div>


                {{-- Step 2 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[340px] h-[75%] lg:h-[65%] lg:translate-y-20 rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            SETUP
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[75%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <rect x="72" y="35" width="96" height="110" rx="4" stroke="currentColor" stroke-width="1.5"/>
                            <rect x="94" y="52" width="52" height="8" rx="2" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M94 78h52M94 94h36M94 110h44" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="120" cy="132" r="5" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M25 70h40M175 70h40M25 90h25M190 90h25" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Login &amp; Ganti Password
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Admin login pakai kredensial awal, lalu wajib ganti password sebelum bisa mengakses dashboard.
                        </p>
                    </div>
                </div>


                {{-- Step 3 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[300px] h-[75%] lg:h-[65%] rounded-[3px] lg:-translate-y-5 bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            UNIT USAHA
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[80%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <path d="M50 145V62l70-35 70 35v83H50Z" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M82 145V92h76v53M120 27v65" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M25 78h25M190 78h25M25 96h25M190 96h25" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Atur Unit Usaha
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Tentukan kategori unit — ritel atau jasa — lalu kelola inventaris atau pesanan layanan sesuai jenisnya.
                        </p>
                    </div>
                </div>


                {{-- Step 4 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[300px] h-[75%] lg:h-[65%] lg:-translate-y-16 rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            TRANSAKSI
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[82%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <rect x="35" y="40" width="170" height="105" rx="3" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M55 68h130M55 90h75M55 112h105" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M25 55h10M205 55h10M25 78h10M205 78h10M25 101h10M205 101h10" stroke="currentColor" stroke-width="1.5"/>
                            <circle cx="178" cy="112" r="12" stroke="currentColor" stroke-width="1.5"/>
                            <path d="m172 112 4 4 8-9" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Catat Transaksi &amp; Pembelian
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Catat transaksi harian, transaksi berulang, dan pembelian ke vendor secara terpusat.
                        </p>
                    </div>
                </div>


                {{-- Step 5 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[300px] h-[75%] lg:h-[65%] lg:translate-y-10 rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            LAPORAN
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[80%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <path d="M42 140V45h156v95H42Z" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M65 115V92M95 115V72M125 115V82M155 115V55M185 115V40" stroke="currentColor" stroke-width="7"/>
                            <path d="M55 132h130" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M25 60h17M198 60h17M25 78h17M198 78h17" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Pantau dan Laporkan
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Lihat statistik unit secara real-time, terbitkan dokumen resmi, lalu ekspor data kapan saja.
                        </p>
                    </div>
                </div>


                {{-- Step 6 --}}
                <div class="howitworks-card snap-center shrink-0 w-[85vw] sm:w-[300px] h-[75%] lg:h-[65%] lg:-translate-y-4 rounded-[3px] bg-blue-800 dark:bg-blue-950 p-5 relative overflow-hidden flex flex-col">

                    <div class="relative z-10 flex items-start justify-between">
                        <span class="px-2.5 py-1 rounded-[2px] bg-white/10 text-white text-[10px] font-semibold uppercase tracking-wide">
                            KEAMANAN
                        </span>

                        <span class="flex h-9 w-9 items-center justify-center rounded-[3px] bg-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="#1e3a8a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M17 7H8M17 7V16"/></svg>
                        </span>
                    </div>

                    <div class="absolute inset-x-0 top-[18%] bottom-[25%] flex items-center justify-center pointer-events-none">
                        <svg class="w-[78%] h-auto text-white/35" viewBox="0 0 240 180" fill="none">
                            <path d="M120 25 184 48v43c0 38-25 57-64 70-39-13-64-32-64-70V48l64-23Z"
                                stroke="currentColor" stroke-width="1.5"/>
                            <path d="m91 92 19 19 40-43" stroke="currentColor" stroke-width="1.5"/>
                            <path d="M45 65H25M195 65h20M45 82H30M195 82h15" stroke="currentColor" stroke-width="1.5"/>
                        </svg>
                    </div>

                    <div class="relative z-10 mt-auto max-w-[95%]">
                        <h3 class="font-display font-medium tracking-tight text-[22px] leading-none text-white">
                            Audit dan Keamanan Data
                        </h3>

                        <p class="mt-4 text-[13px] text-white/80 leading-[1.35] tracking-tight">
                            Setiap perubahan data tercatat dalam log audit yang dapat ditelusuri kapan saja.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </section>

    {{-- ===================== ABOUT ===================== --}}
    <section id="tentang" class="relative bg-blue-950 dark:bg-slate-900 p-6 transition-colors duration-300 overflow-hidden">
        <div class="flex flex-col lg:flex-row min-h-[600px] lg:min-h-screen my-30">

            {{-- Kiri: Gambar (60%) --}}
            <div class="relative w-full lg:w-[60%] h-72 lg:h-[100vh] bg-slate-800 flex-shrink-0 overflow-hidden">
                <img
                    src="{{ asset('images/images (1).jpg') }}"
                    alt="About"
                    class="absolute inset-0 w-full h-full object-cover opacity-50 hover:opacity-100 transition-opacity duration-300"
                >
            </div>

            {{-- Kanan: Konten (40%) --}}
            <div class="relative flex flex-col justify-between w-full lg:w-[40%] px-4 py-16 lg:pl-6 lg:pr-16 lg:py-6">

                {{-- Heading + deskripsi --}}
                <div>
                    <h2 data-reveal-text class="font-display text-2xl lg:text-4xl font-medium text-white leading-tighter tracking-tighter">
                        Built for Real-World Financial Systems
                    </h2>
                </div>

                {{-- Deskripsi + tombol --}}
                <div class="mt-12 lg:mt-0 flex flex-col">
                    <p data-reveal-text class="text-white text-xs font-semibold leading-tight tracking-tight">
                        Platform ini memungkinkan institusi untuk mengelola aset digital dalam
                        lingkungan yang terstruktur dan patuh. Dari penerbitan aset hingga eksekusi,
                        setiap komponen dirancang untuk terintegrasi secara mulus dengan sistem
                        dan alur kerja keuangan yang sudah ada.
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
                        Ada pertanyaan? Cek hal sering ditanyakan.
                    </h2>
                </div>
            </div>

            {{-- Bawah: label kiri + accordion kanan --}}
            <div class="grid grid-cols-1 lg:grid-cols-[440px_1fr] gap-4 lg:gap-10 items-start pt-8">
                <span data-reveal-text class="text-sm font-semibold pt-7 text-blue-900 dark:text-white">
                    Pertanyaan Umum
                </span>

                <div class="faq-list flex flex-col">

                    <div class="faq-item border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            class="faq-trigger group flex w-full items-center justify-between py-6 text-left"
                        >
                            <span data-reveal-text class="text-base lg:text-base tracking-tighter font-semibold text-blue-900 dark:text-white">
                                Bagaimana cara mendaftarkan unit usaha baru?
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
                                    Master Admin dapat menambahkan unit usaha baru melalui menu Master Management &gt; Unit Usaha, mengisi data dasar beserta kategorinya (ritel atau jasa), lalu membuat akun Admin Unit penanggung jawabnya.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            class="faq-trigger group flex w-full items-center justify-between py-6 text-left"
                        >
                            <span data-reveal-text class="text-base lg:text-base tracking-tighter font-semibold text-blue-900 dark:text-white">
                                Apakah laporan keuangan bisa digabung antar unit usaha?
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
                                    Bisa. Master Admin punya menu Statistik Usaha dan Dokumen Resmi lintas unit yang merangkum data dari seluruh unit usaha, selain bisa diekspor per unit lewat menu Export Data.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            class="faq-trigger group flex w-full items-center justify-between py-6 text-left"
                        >
                            <span data-reveal-text class="text-base lg:text-base tracking-tighter font-semibold text-blue-900 dark:text-white">
                                Berapa jumlah admin yang bisa mengakses satu unit usaha?
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
                                    Tidak ada batasan jumlah admin. Satu unit usaha bisa memiliki lebih dari satu Admin Unit, dan seluruh akunnya dibuat serta dikelola oleh Master Admin.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="faq-item border-b border-slate-200 dark:border-slate-800">
                        <button
                            type="button"
                            class="faq-trigger group flex w-full items-center justify-between py-6 text-left"
                        >
                            <span data-reveal-text class="text-base lg:text-base tracking-tighter font-semibold text-blue-900 dark:text-white">
                                Apakah data transaksi tersimpan aman?
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
                                <p class="pb-6 text-sm lg:text-sm tracking-tight text-blue-900/70  dark:text-slate-400 max-w-2xl">
                                    Ya. Setiap akun hanya bisa aktif di satu sesi login — login di perangkat lain otomatis mengakhiri sesi sebelumnya — dan seluruh aktivitas serta perubahan data tercatat dalam Audit Log yang bisa ditelusuri kapan saja.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    {{-- ===================== FOOTER ===================== --}}
    <footer class="bg-slate-950 dark:bg-slate-900 pt-20 pb-5 px-6 lg:px-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_1fr] gap-12 lg:gap-28 pb-40">

                {{-- Kiri: Heading + Subscribe Form --}}
                <div id="kontak">
                    <h2 class="font-display text-3xl font-medium leading-none tracking-tighter text-white">
                        Hubungi <br class="hidden sm:block" />
                        Admin Pusat SIMS
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
                src="{{ asset('images/symbol-gold.png') }}" 
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