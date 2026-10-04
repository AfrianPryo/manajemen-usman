<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Unit' }} - Usaha Mandiri Sekolah</title>
    {{-- Ikon tab browser: pakai logo yang diatur di Pengaturan (Setting 'app_logo'),
         fallback ke favicon.svg bawaan kalau belum ada logo yang diupload. Nama
         file logo hasil upload selalu unik (di-hash oleh store()), jadi URL-nya
         otomatis berubah saat logo diganti dan browser tidak menampilkan ikon lama. --}}
    @php $faviconLogo = \App\Models\Setting::get('app_logo'); @endphp
    @if ($faviconLogo)
        <link rel="icon" href="{{ asset('storage/' . $faviconLogo) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    <script>
        // Terapkan preferensi tema SEBELUM CSS/HTML lain dirender, supaya
        // tidak ada "kedipan" mode terang sesaat sebelum Alpine menyala
        // (flash of unstyled/incorrect theme). Sumber kebenaran tema tetap
        // sama seperti tombol toggle di header: localStorage 'theme', lalu
        // fallback ke preferensi sistem (prefers-color-scheme).
        (function () {
            try {
                const stored = localStorage.getItem('theme');
                const isDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            } catch (e) {}
        })();
    </script>

    <style>
        /* Tampilan panel dibuat terlihat seperti zoom browser 90%, padahal zoom
           browser tetap 100%, supaya area kerja lebih luas. Ubah angka di bawah
           untuk mengatur tingkat pengecilan. Dipasang di root: seluruh ukuran Tailwind berbasis
           rem (spacing, teks, lebar sidebar, ikon) ikut mengecil seragam.
           Hanya berlaku di layout ini, tidak menyentuh halaman login/landing. */
        html {
            font-size: 90%;
        }

        #main-content:not(.is-ready) {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }

        /* Sembunyikan scrollbar tapi area tetap bisa discroll (mouse/trackpad/keyboard) */
        .no-scrollbar {
            scrollbar-width: none;       /* Firefox */
            -ms-overflow-style: none;    /* IE / Edge lama */
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;               /* Chrome, Safari, Edge (WebKit) */
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 dark:bg-slate-950 font-sans antialiased">
    @php
        $appName    = \App\Models\Setting::get('app_name', 'USMAN - Usaha Mandiri Sekolah');
        // Key yang sama sudah dibaca di <head> ($faviconLogo) -> dipakai ulang,
        // tidak perlu lookup cache kedua kali per render.
        $appLogo    = $faviconLogo;
        $appLogoUrl = $appLogo ? asset('storage/' . $appLogo) : asset('favicon.svg');
        // Foto profil admin master (diatur di Pengaturan Sistem > Profil Admin)
        $sidebarPhotoPath = auth()->user()->profile_photo_path ?? null;
        $sidebarPhotoUrl  = $sidebarPhotoPath ? asset('storage/' . $sidebarPhotoPath) : null;
        $sidebarInitial   = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
        $slugUnitAktif = request()->route('unit')?->slug ?? auth()->user()?->unit?->slug;

        // Kategori unit yang SEDANG DIBUKA (bukan kategori unit
        // milik user login) -- dipakai untuk menyaring item menu
        // yang punya key 'unit_category' (lihat config/menu.php,
        // grup "Manajemen Layanan"). Konsisten dengan pola
        // $slugUnitAktif di atas: kalau route-nya sedang membuka
        // unit tertentu (Master Admin memantau unit lain), pakai
        // kategori unit ITU, bukan kategori unit user login.
        $categoryUnitAktif = request()->route('unit')?->category ?? auth()->user()?->unit?->category;
        // Status aktif sidebar yang paham sub-halaman. Menu bertipe "section"
        // (route berakhiran .index, mis. master.documents.index) tetap menyala
        // saat user berada di sub-halamannya (generate, history, templates, dst.).
        // Kalau route yang sedang dibuka punya entri menu sendiri, entri itu
        // yang menang -- supaya tidak ada dua menu menyala bersamaan.
        $sidebarMenuRoutes = collect(config('menu'))
            ->flatMap(fn ($m) => isset($m['children']) ? collect($m['children'])->pluck('route') : [$m['route'] ?? null])
            ->filter()->values()->all();
        $sidebarCurrentRoute = request()->route()?->getName();
        $sidebarIsActive = function (string $route) use ($sidebarMenuRoutes, $sidebarCurrentRoute): bool {
            if (request()->routeIs($route.'*')) {
                return true;
            }
            if (! str_ends_with($route, '.index') || in_array($sidebarCurrentRoute, $sidebarMenuRoutes, true)) {
                return false;
            }
            return request()->routeIs(\Illuminate\Support\Str::beforeLast($route, '.index').'.*');
        };
        // Sidebar Unit tidak pernah menautkan ke Pengaturan Sistem
        // milik Master (route itu di-guard middleware role:master-admin
        // dan memang di luar cakupan dashboard unit). Selalu arahkan
        // ke Profil Saya milik unit yang sedang login.
        //
        // unit.profile.index adalah profil PRIBADI user yang sedang
        // login (auth()->id()) -- BUKAN profil unit yang sedang
        // dipantau. Jadi khusus link ini tetap pakai unit milik
        // user login (auth()->user()->unit), bukan $slugUnitAktif, agar
        // Master Admin yang sedang memantau unit lain tidak diarahkan
        // ke halaman yang salah / 403.
        $settingsLabel = 'Profil Saya';
        $hasSettingsRoute = Route::has('unit.profile.index');
        $slugUnitProfil = auth()->user()?->unit?->slug ?? $slugUnitAktif;
        $settingsUrl = ($hasSettingsRoute && $slugUnitProfil)
            ? route('unit.profile.index', ['unit' => $slugUnitProfil])
            : '#';
        $settingsActive = request()->routeIs('unit.profile.*');
        // ---- Status menu aktif & persist sidebar ------------------------------------
        // Sidebar di-persist (x-persist) lintas wire:navigate, jadi HTML-nya hanya
        // dipakai saat load pertama / reload penuh. Setelah itu daftar menu aktif
        // diambil dari #sidebar-state (dirender di <main>, selalu baru tiap halaman).
        $sidebarActive = [];   // diisi di loop menu: nama route yang menyala

        $navLinkOn  = 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-semibold shadow-2xs';
        $navLinkOff = 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-100';
        $navIconOn  = 'text-slate-900 dark:text-white';
        $navIconOff = 'text-slate-400 dark:text-slate-500';

        // Kunci persist. Berubah (=> sidebar dibangun ulang dengan data baru) hanya kalau
        // identitas/tampilan sidebar berubah: user, unit yang dibuka, nama/logo aplikasi,
        // atau nama/foto profil -- jadi hasil edit di halaman Pengaturan/Profil tetap muncul.
        $sidebarKey = 'unit-' . auth()->id() . '-' . $slugUnitAktif . '-' . $categoryUnitAktif . '-' . substr(md5(implode('|', [
                $appName, $appLogo, $sidebarPhotoPath, auth()->user()->name, auth()->user()->email])), 0, 8);
    @endphp

    {{-- Sidebar di-persist lintas wire:navigate (lihat x-persist di bawah): elemen DOM-nya
         DIPINDAHKAN ke halaman baru, bukan dibuat ulang. Karena itu state-nya self-contained
         (tidak bergantung x-data milik parent yang ikut diganti), dan komunikasi dengan
         header memakai event window (sidebar-open). Fungsi ini identik di layout Master &
         Unit, jadi cukup dieksekusi sekali (data-navigate-once). --}}
    <script data-navigate-once>
        function usmanSidebar() {
            return {
                sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
                mobileSidebarOpen: false,
                settingsOn: document.getElementById('sidebar-state')?.dataset.settings === '1',

                init() {
                    this.$watch('sidebarCollapsed', v => localStorage.setItem('sidebarCollapsed', v));
                },

                // Dipanggil setiap selesai navigasi. Daftar menu aktif dihitung server
                // (logika route-name yang sama seperti sebelumnya) & dititipkan di
                // #sidebar-state pada halaman baru; di sini hanya menukar class link yang
                // statusnya berubah -- tanpa fetch, tanpa render ulang.
                syncActive() {
                    const state = document.getElementById('sidebar-state');
                    if (!state || !this.$refs.nav) return;

                    const active = JSON.parse(state.dataset.active || '[]');
                    this.settingsOn = state.dataset.settings === '1';

                    const nav = this.$refs.nav;
                    const cls = key => nav.dataset[key].split(' ');
                    const swap = (el, on, onKey, offKey) => {
                        el.classList.remove(...cls(on ? offKey : onKey));
                        el.classList.add(...cls(on ? onKey : offKey));
                    };

                    nav.querySelectorAll('a[data-nav]').forEach(a => {
                        const on = active.includes(a.dataset.nav);
                        if ((a.dataset.on === '1') === on) return;
                        a.dataset.on = on ? '1' : '0';
                        swap(a, on, 'linkOn', 'linkOff');
                        const icon = a.querySelector('[data-nav-icon]');
                        if (icon) swap(icon, on, 'iconOn', 'iconOff');
                    });
                },
            };
        }
    </script>

    {{-- x-data kosong: tetap jadi scope Alpine untuk header (hamburger) & isi halaman ($slot).
         State sidebar sendiri ada di wrapper x-persist di bawah. --}}
    <div class="flex h-screen overflow-hidden font-sans" x-data>
        {{-- x-persist: elemen ini dipertahankan antar halaman (tidak ter-refresh). "contents" =
             wrapper tidak mempengaruhi layout flex; aside & backdrop tetap anak langsung flex. --}}
        <div x-persist="sidebar-{{ $sidebarKey }}"
             class="contents"
             x-data="usmanSidebar()"
             @keydown.escape.window="mobileSidebarOpen = false"
             x-on:sidebar-open.window="mobileSidebarOpen = true"
             x-on:livewire:navigated.window="syncActive()">

        {{-- Backdrop (mobile only) --}}
        <div x-show="mobileSidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileSidebarOpen = false"
             class="fixed inset-0 z-40 bg-slate-900/50 dark:bg-black/60 md:hidden"
             aria-hidden="true"></div>

        {{-- Sidebar --}}
        {{-- Sidebar ini KHUSUS dashboard Unit Usaha, terpisah dari sidebar
             Admin Master (lihat components/layouts/app.blade.php). Sumber
             datanya tetap config('menu') yang sama (satu sumber kebenaran
             untuk seluruh rute aplikasi), tapi di sini secara eksplisit
             HANYA menu dengan rute berawalan "unit." yang dirender — menu
             khusus Master tidak akan pernah muncul di sidebar ini.

             Perilaku collapse/expand (desktop) & hamburger responsive
             (mobile) sengaja dibuat SAMA PERSIS dengan sidebar Master
             (lihat components/layouts/app.blade.php) supaya pengalaman
             navigasi konsisten antara dua peran ini. --}}
        <aside
            :class="[
                sidebarCollapsed ? 'md:w-16' : 'md:w-64',
                mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
            ]"
            style="transition: width 280ms cubic-bezier(0.4, 0, 0.2, 1), transform 280ms cubic-bezier(0.4, 0, 0.2, 1); will-change: width, transform;"
            class="fixed inset-y-0 left-0 z-50 w-64 md:relative md:z-auto bg-white dark:bg-slate-900 border-r border-slate-200/70 dark:border-slate-800 text-slate-700 dark:text-slate-300 flex-shrink-0 flex flex-col justify-between select-none overflow-hidden">

            {{-- Logo Header (fixed, TIDAK ikut ter-scroll) --}}
            <div class="h-12 flex items-center justify-between px-4 font-bold text-sm text-slate-900 dark:text-white border-b border-slate-100 dark:border-slate-800 shrink-0 tracking-tight">
                <span class="flex items-center gap-2 overflow-hidden">
                    {{-- Logo custom (hasil upload di Pengaturan Sistem) dibungkus lingkaran
                         putih supaya tetap kontras & jelas terlihat baik di mode terang
                         maupun gelap, apa pun warna dasar logonya. Ikon favicon default
                         tidak dibungkus karena memang sudah didesain untuk kedua tema. --}}
                    @if ($appLogo)
                        <span class="h-6 w-6 rounded-full bg-white shrink-0 overflow-hidden flex items-center justify-center ring-1 ring-black/5">
                            <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-0.5">
                        </span>
                    @else
                        <img 
                            src="{{ $appLogoUrl }}" 
                            alt="{{ $appName }}" 
                            class="h-6 w-6 rounded-full object-contain shrink-0"
                        />
                    @endif
                    <span x-show="!sidebarCollapsed || mobileSidebarOpen"
                          x-transition:enter="transition-opacity duration-150 delay-140"
                          x-transition:enter-start="opacity-0"
                          x-transition:enter-end="opacity-100"
                          x-transition:leave="transition-opacity duration-75"
                          x-transition:leave-start="opacity-100"
                          x-transition:leave-end="opacity-0"
                          x-cloak class="truncate max-w-[140px]">{{ $appName }}</span>
                </span>

                {{-- Tombol Toggle Collapse/Expand (desktop) --}}
                <button
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    x-show="!sidebarCollapsed"
                    x-cloak
                    type="button"
                    title="Ciutkan sidebar"
                    class="hidden md:inline-flex shrink-0 p-1 rounded-md text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                    <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                </button>

                {{-- Tombol Tutup Sidebar (mobile) --}}
                <button
                    @click="mobileSidebarOpen = false"
                    type="button"
                    title="Tutup menu"
                    class="md:hidden shrink-0 p-1 rounded-md text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            {{-- Tombol Expand saat collapsed (fixed, tampil di bawah logo, terpusat, desktop only) --}}
            <div x-show="sidebarCollapsed" x-cloak class="hidden md:flex justify-center py-1.5 border-b border-slate-100 dark:border-slate-800 shrink-0">
                <button
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    type="button"
                    title="Perluas sidebar"
                    class="p-1 rounded-md text-slate-400 dark:text-slate-500 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                    <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                </button>
            </div>

            {{-- Area menu yang bisa discroll (logo & footer tetap diam) --}}
            <div wire:navigate:scroll
                x-init="$nextTick(() => { $el.scrollTop = Number(sessionStorage.getItem('unitSidebarScrollTop') || 0) })"
                @scroll="sessionStorage.setItem('unitSidebarScrollTop', $el.scrollTop)"
                class="no-scrollbar flex-1 overflow-y-auto overflow-x-hidden">
                <nav class="py-4 px-2.5 space-y-0.5" @click="mobileSidebarOpen = false"
                     x-ref="nav"
                     data-link-on="{{ $navLinkOn }}" data-link-off="{{ $navLinkOff }}"
                     data-icon-on="{{ $navIconOn }}" data-icon-off="{{ $navIconOff }}">
                    @foreach(config('menu') as $item)
                        @php
                            $label = strtolower($item['label'] ?? '');
                            $route = strtolower($item['route'] ?? '');
                            $isSettings = str_contains($label, 'pengaturan') || str_contains($label, 'setting') || str_contains($route, 'settings');
                        @endphp

                        @continue($isSettings)

                        @php
                            $canSee = is_null($item['roles']) || auth()->user()?->hasAnyRole($item['roles']) || auth()->user()?->isMasterAdmin();

                            // Filter tambahan berdasarkan kategori unit. Item/grup
                            // TANPA key 'unit_category' selalu lolos (perilaku
                            // lama, tidak berubah). Item DENGAN key ini hanya
                            // lolos kalau cocok dengan kategori unit yang sedang
                            // dibuka.
                            if ($canSee && isset($item['unit_category']) && $item['unit_category'] !== $categoryUnitAktif) {
                                $canSee = false;
                            }
                        @endphp

                        @if($canSee)
                            @if(isset($item['children']))
                                @php
                                    $filteredChildren = collect($item['children'])
                                        ->filter(fn($child) => str_starts_with($child['route'] ?? '', 'unit.'))
                                        ->reject(function($child) {
                                            $cLabel = strtolower($child['label'] ?? '');
                                            $cRoute = strtolower($child['route'] ?? '');
                                            return str_contains($cLabel, 'pengaturan') || str_contains($cLabel, 'setting') || str_contains($cRoute, 'settings');
                                        })
                                        ->filter(function($child) use ($categoryUnitAktif) {
                                            return !isset($child['unit_category']) || $child['unit_category'] === $categoryUnitAktif;
                                        });
                                @endphp

                                @if($filteredChildren->count() > 0)
                                    <div class="pt-1.5 pb-0.5 first:pt-0">
                                        {{-- Header Kategori (disembunyikan saat collapsed) --}}
                                        <div x-show="!sidebarCollapsed || mobileSidebarOpen"
                                             x-transition:enter="transition-all duration-150 delay-140 ease-out"
                                             x-transition:enter-start="opacity-0 -translate-x-1"
                                             x-transition:enter-end="opacity-100 translate-x-0"
                                             x-transition:leave="transition-opacity duration-75 ease-in"
                                             x-transition:leave-start="opacity-100"
                                             x-transition:leave-end="opacity-0"
                                             x-cloak class="px-2.5 pb-1 text-[9px] font-bold text-slate-400 dark:text-slate-500 tracking-wider uppercase">
                                            {{ $item['label'] }}
                                        </div>

                                        {{-- Sub Menu Items --}}
                                        <div class="space-y-0.5">
                                            @foreach($filteredChildren as $child)
                                                @continue(!is_null($child['roles']) && !auth()->user()?->hasAnyRole($child['roles']) && !auth()->user()?->isMasterAdmin())
                                                @php
                                                    $childActive = $sidebarIsActive($child['route']);
                                                    if ($childActive) { $sidebarActive[] = $child['route']; }
                                                    // Semua item di sidebar ini sudah dipastikan berawalan
                                                    // "unit." (lihat filter di atas). Slug diambil dari unit
                                                    // yang SEDANG DIBUKA (route-model-binding {unit:slug}),
                                                    // bukan dari unit milik user login — supaya link tetap
                                                    // benar saat halaman ini dibuka Master Admin yang sedang
                                                    // memantau unit lain.
                                                    $childRouteParams = ['unit' => $slugUnitAktif];
                                                @endphp
                                                <a wire:navigate href="{{ route($child['route'], $childRouteParams) }}"
                                                   data-nav="{{ $child['route'] }}" data-on="{{ $childActive ? 1 : 0 }}"
                                                   title="{{ $child['label'] }}"
                                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors duration-150 {{ $childActive ? $navLinkOn : $navLinkOff }}">
                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                        @if(isset($child['icon']))
                                                            <span data-nav-icon class="w-5 h-5 flex items-center justify-center shrink-0 {{ $childActive ? $navIconOn : $navIconOff }}">
                                                                <x-dynamic-component :component="'heroicon-o-'.$child['icon']" class="w-4 h-4" />
                                                            </span>
                                                        @endif
                                                        <span x-show="!sidebarCollapsed || mobileSidebarOpen"
                                                              x-transition:enter="transition-all duration-150 delay-140 ease-out"
                                                              x-transition:enter-start="opacity-0 -translate-x-1"
                                                              x-transition:enter-end="opacity-100 translate-x-0"
                                                              x-transition:leave="transition-opacity duration-75 ease-in"
                                                              x-transition:leave-start="opacity-100"
                                                              x-transition:leave-end="opacity-0"
                                                              x-cloak class="truncate">{{ $child['label'] }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                {{-- Single Menu Item --}}
                                @continue(!str_starts_with($item['route'] ?? '', 'unit.'))
                                @php
                                    $itemActive = $sidebarIsActive($item['route']);
                                    if ($itemActive) { $sidebarActive[] = $item['route']; }
                                    $itemRouteParams = ['unit' => $slugUnitAktif];
                                @endphp
                                <a wire:navigate href="{{ route($item['route'], $itemRouteParams) }}"
                                   data-nav="{{ $item['route'] }}" data-on="{{ $itemActive ? 1 : 0 }}"
                                   title="{{ $item['label'] }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors duration-150 {{ $itemActive ? $navLinkOn : $navLinkOff }}">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        @if(isset($item['icon']))
                                            <span data-nav-icon class="w-5 h-5 flex items-center justify-center shrink-0 {{ $itemActive ? $navIconOn : $navIconOff }}">
                                                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="w-4 h-4" />
                                            </span>
                                        @endif
                                        <span x-show="!sidebarCollapsed || mobileSidebarOpen"
                                              x-transition:enter="transition-all duration-150 delay-140 ease-out"
                                              x-transition:enter-start="opacity-0 -translate-x-1"
                                              x-transition:enter-end="opacity-100 translate-x-0"
                                              x-transition:leave="transition-opacity duration-75 ease-in"
                                              x-transition:leave-start="opacity-100"
                                              x-transition:leave-end="opacity-0"
                                              x-cloak class="truncate">{{ $item['label'] }}</span>
                                    </div>
                                    <svg x-show="!sidebarCollapsed || mobileSidebarOpen"
                                         x-transition:enter="transition-opacity duration-150 delay-140 ease-out"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition-opacity duration-75 ease-in"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         x-cloak class="w-3 h-3 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Bottom User Profile & Pop-up Menu Profil Saya --}}
            <div class="p-2 border-t border-slate-100 dark:border-slate-800 relative" x-data="{ userMenuOpen: false }">
                {{-- Pop-up Menu Floating Upward --}}
                <div x-show="userMenuOpen"
                    @click.outside="userMenuOpen = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    :class="(sidebarCollapsed && !mobileSidebarOpen) ? 'left-2 w-12' : 'left-2 right-2 w-56'"
                    class="absolute bottom-full mb-1.5 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-700 rounded-sm shadow-sm/10 dark:shadow-black/40 p-1.5 z-50 text-slate-800 dark:text-slate-200">

                    {{-- ====== MODE COLLAPSED: hanya ikon ====== --}}
                    <template x-if="sidebarCollapsed && !mobileSidebarOpen">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center font-bold text-[11px] shrink-0 overflow-hidden mb-0.5"
                                 title="{{ auth()->user()->name }}">
                                @if ($sidebarPhotoUrl)
                                    <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                                @else
                                    {{ $sidebarInitial }}
                                @endif
                            </div>

                            <div class="w-6 h-px bg-slate-100 dark:bg-slate-700"></div>

                            {{-- Link Profil Saya (ikon saja) --}}
                            <a wire:navigate href="{{ $settingsUrl }}"
                               @click="userMenuOpen = false; mobileSidebarOpen = false"
                               title="{{ $settingsLabel }}"
                               class="flex items-center justify-center w-8 h-8 rounded-lg transition-all"
                               :class="settingsOn ? 'bg-slate-900 dark:bg-slate-700 text-white shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100'">
                                <x-heroicon-o-user-circle class="w-4 h-4" />
                            </a>

                            {{-- Tombol Logout (ikon saja) --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" title="Log out"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:text-rose-700 dark:hover:text-rose-400 transition-all">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </template>

                    {{-- ====== MODE EXPANDED: kartu lengkap (perilaku lama) ====== --}}
                    <template x-if="!(sidebarCollapsed && !mobileSidebarOpen)">
                        <div>
                            {{-- Detail User --}}
                            <div class="flex items-center gap-2.5 p-2 border-b border-slate-100 dark:border-slate-800 mb-1">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200/80 dark:border-slate-700 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden">
                                    @if ($sidebarPhotoUrl)
                                        <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                                    @else
                                        {{ $sidebarInitial }}
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate leading-tight">{{ auth()->user()->email ?? auth()->user()->getRoleNames()->first() }}</p>
                                </div>
                            </div>

                            {{-- Link Profil Saya --}}

                            <a wire:navigate href="{{ $settingsUrl }}"
                            @click="userMenuOpen = false; mobileSidebarOpen = false"
                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs transition-all"
                               :class="settingsOn ? 'bg-slate-900 dark:bg-slate-700 text-white font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100/80 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 font-medium'">
                                <x-heroicon-o-user-circle class="w-3.5 h-3.5"
                               x-bind:class="settingsOn ? 'text-white' : 'text-slate-400 dark:text-slate-500'" />
                                <span>{{ $settingsLabel }}</span>
                            </a>

                            {{-- Tombol Logout --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-slate-600 dark:text-slate-400 hover:bg-rose-50 dark:hover:bg-rose-950/50 hover:text-rose-700 dark:hover:text-rose-400 transition-all text-left font-medium group">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 group-hover:text-rose-600 dark:group-hover:text-rose-400" />
                                    <span>Log out</span>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>

                {{-- Trigger Button --}}
                <button @click="userMenuOpen = !userMenuOpen"
                        title="{{ auth()->user()->name }}"
                        class="w-full flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors text-left focus:outline-none border border-transparent hover:border-slate-200/60 dark:hover:border-slate-700">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-6.5 h-6.5 rounded-md bg-slate-900 dark:bg-slate-700 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs overflow-hidden">
                            @if ($sidebarPhotoUrl)
                                <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                            @else
                                {{ $sidebarInitial }}
                            @endif
                        </div>
                        <span x-show="!sidebarCollapsed || mobileSidebarOpen"
                              x-transition:enter="transition-all duration-150 delay-140 ease-out"
                              x-transition:enter-start="opacity-0 -translate-x-1"
                              x-transition:enter-end="opacity-100 translate-x-0"
                              x-transition:leave="transition-opacity duration-75 ease-in"
                              x-transition:leave-start="opacity-100"
                              x-transition:leave-end="opacity-0"
                              x-cloak class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">{{ auth()->user()->name }}</span>
                    </div>
                    <x-heroicon-o-chevron-up-down
                        x-show="!sidebarCollapsed || mobileSidebarOpen"
                        x-transition:enter="transition-opacity duration-150 delay-140 ease-out"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-75 ease-in"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-cloak
                        class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" />
                </button>
            </div>
        </aside>
        </div>{{-- /x-persist sidebar --}}

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-[#f8f9fa] dark:bg-slate-950 min-w-0">

            {{-- HEADER STYLE REFERENSI (BREADCRUMB HEADER) --}}
            <header class="h-12 bg-white dark:bg-slate-900 border-b border-slate-200/70 dark:border-slate-800 flex items-center justify-between px-3 md:px-6 shrink-0 gap-2">

                {{-- Left: Hamburger (mobile) + Path Breadcrumb --}}
                <div class="flex items-center gap-2 min-w-0">

                    {{-- Tombol Buka Sidebar (mobile only) --}}
                    <button
                        @click="$dispatch('sidebar-open')"
                        type="button"
                        title="Buka menu"
                        class="md:hidden shrink-0 p-1.5 -ml-1 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors focus:outline-none">
                        <x-heroicon-o-bars-3 class="w-5 h-5" />
                    </button>

                    @php
                        // Breadcrumb bertingkat: kalau halaman ini sub-halaman dari sebuah menu
                        // (mis. Dokumen > Buat Dokumen Resmi), tampilkan menu induknya sebagai
                        // link. Bisa dioverride dari halaman lewat variabel layout $parent
                        // (label) dan $parentUrl.
                        $crumbParent = null;
                        $crumbParentUrl = null;
                        if (isset($parent) && $parent) {
                            $crumbParent = ['label' => $parent];
                            $crumbParentUrl = $parentUrl ?? null;
                        } elseif ($sidebarCurrentRoute && ! in_array($sidebarCurrentRoute, $sidebarMenuRoutes, true)) {
                            $crumbParent = collect(config('menu'))
                                ->flatMap(fn ($m) => isset($m['children']) ? $m['children'] : [$m])
                                ->first(function ($m) {
                                    $r = $m['route'] ?? null;
                                    return $r
                                        && str_starts_with($r, 'unit.')
                                        && str_ends_with($r, '.index')
                                        && ! str_contains(strtolower($r), 'settings')
                                        && request()->routeIs(\Illuminate\Support\Str::beforeLast($r, '.index').'.*');
                                });
                            if ($crumbParent) {
                                $r = $crumbParent['route'];
                                $crumbParentUrl = Route::has($r) ? route($r, ['unit' => $slugUnitAktif]) : null;
                            }
                        }
                        if ($crumbParent && ($crumbParent['label'] ?? null) === ($title ?? null)) {
                            $crumbParent = null;
                        }
                    @endphp
                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 dark:text-slate-400 min-w-0">

                        {{-- Parent / Kategori --}}
                        <div class="hidden sm:flex items-center gap-1.5 px-2 py-1 text-slate-700 dark:text-slate-300 font-semibold shrink-0">
                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" />
                            <span>{{ $category ?? 'Unit Usaha' }}</span>
                        </div>

                        {{-- Separator Slash --}}
                        <span class="hidden sm:inline text-slate-300 dark:text-slate-600 font-normal">/</span>

                        {{-- Menu induk (hanya muncul di sub-halaman, mis. Dokumen > Buat Dokumen Resmi) --}}
                        @if($crumbParent)
                            @if($crumbParentUrl)
                                <a wire:navigate href="{{ $crumbParentUrl }}" class="hidden sm:flex items-center gap-1.5 px-2 py-1 rounded-md text-slate-500 dark:text-slate-400 font-semibold shrink-0 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-100 transition-colors">
                                    @if(isset($crumbParent['icon']))
                                        <x-dynamic-component :component="'heroicon-o-'.$crumbParent['icon']" class="w-3.5 h-3.5" />
                                    @endif
                                    <span>{{ $crumbParent['label'] }}</span>
                                </a>
                            @else
                                <div class="hidden sm:flex items-center gap-1.5 px-2 py-1 text-slate-500 dark:text-slate-400 font-semibold shrink-0">
                                    <span>{{ $crumbParent['label'] }}</span>
                                </div>
                            @endif
                            <span class="hidden sm:inline text-slate-300 dark:text-slate-600 font-normal">/</span>
                        @endif

                        {{-- Current Page Title --}}
                        <div class="flex items-center gap-1.5 text-slate-900 dark:text-white font-bold min-w-0">
                            <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300 shrink-0" />
                            <span class="truncate">{{ $title ?? 'Dashboard Unit' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Right: Light/Dark Mode, Notification Component & User Role Badge --}}
                <div class="flex items-center gap-3 shrink-0">

                    {{-- Toggle Light / Dark Mode --}}
                    <div x-data="{
                        darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
                        toggle() {
                            this.darkMode = !this.darkMode;
                            if (this.darkMode) {
                                document.documentElement.classList.add('dark');
                                localStorage.setItem('theme', 'dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                                localStorage.setItem('theme', 'light');
                            }
                        }
                    }" x-init="
                        if (darkMode) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    ">
                        <button @click="toggle()"
                                class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors focus:outline-none"
                                :title="darkMode ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'">

                            {{-- Icon Moon --}}
                            <x-heroicon-o-moon x-show="!darkMode" class="w-4 h-4" />

                            {{-- Icon Sun --}}
                            <x-heroicon-s-sun x-show="darkMode" x-cloak class="w-4 h-4 text-amber-400" />
                        </button>
                    </div>

                    {{-- Tombol Tutorial: selalu terlihat di header (bukan hanya di sidebar) supaya
                         panduan video mudah ditemukan. Titik "Baru" hilang setelah halaman
                         Tutorial pernah dibuka di browser ini. --}}
                    @php
                        $tutorialUrl = (Route::has('unit.tutorials.index') && $slugUnitAktif)
                            ? route('unit.tutorials.index', ['unit' => $slugUnitAktif])
                            : (auth()->user()->isMasterAdmin() && Route::has('master.tutorials.index') ? route('master.tutorials.index') : null);
                        $tutorialActive = request()->routeIs('unit.tutorials.*');
                    @endphp
                    @if ($tutorialUrl)
                        <a wire:navigate href="{{ $tutorialUrl }}" data-tour="tutorial-link"
                           x-data="{ seen: true }"
                           x-init="try { seen = localStorage.getItem('usman-tutorial-seen') === '1'; } catch (e) {}; if (@js($tutorialActive)) { try { localStorage.setItem('usman-tutorial-seen', '1'); } catch (e) {}; seen = true; }"
                           title="Tutorial video panduan"
                           class="relative inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-semibold rounded-lg transition-colors focus:outline-none {{ $tutorialActive ? 'text-blue-900 dark:text-sky-300 bg-blue-100 dark:bg-blue-950/60' : 'text-blue-900 dark:text-sky-300 bg-blue-50 dark:bg-blue-950/40 hover:bg-blue-100 dark:hover:bg-blue-950/70' }}">
                            <x-heroicon-o-play-circle class="w-4 h-4" />
                            <span class="hidden sm:inline">Tutorial</span>
                            <span x-show="!seen" x-cloak style="display: none;" class="absolute -top-1 -right-1 flex h-2.5 w-2.5">
                                <span class="absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75 animate-ping"></span>
                                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            </span>
                        </a>
                    @endif

                    {{-- PEMANGGILAN KOMPONEN LIVEWIRE --}}
                    @php
                        // Sama seperti $viewAllUrl di components.layouts.app: sebelumnya
                        // tidak pernah di-set page manapun (selalu '#'). Sekarang diarahkan
                        // ke App\Livewire\Unit\Notifications\Index untuk unit yang SEDANG
                        // DIBUKA ($slugUnitAktif, dihitung di atas) -- notifikasi sendiri
                        // tetap milik akun yang login, param unit di sini cuma supaya URL-nya
                        // konsisten dengan pola route unit.* lainnya di sidebar.
                        $notifViewAllUrl = $viewAllUrl
                            ?? ((Route::has('unit.notifications.index') && $slugUnitAktif)
                                ? route('unit.notifications.index', ['unit' => $slugUnitAktif])
                                : '#');
                    @endphp
                    <livewire:notification-sidebar
                        :role="$role ?? 'unit'"
                        :view-all-url="$notifViewAllUrl"
                    />

                    @if(auth()->user()->isMasterAdmin())
                        <a wire:navigate href="{{ route('master.dashboard') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-50 dark:bg-slate-900/40 border border-neutral-200 dark:border-slate-700 rounded-sm hover:bg-neutral-100 dark:hover:bg-slate-900 transition-all shadow-sm shadow-black/[0.02]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                            <span class="hidden sm:inline">Kembali ke Master</span>
                        </a>
                    @endif

                </div>
            </header>

            {{-- Main Scroll Content Area --}}
            <main class="no-scrollbar flex-1 overflow-y-auto p-2 bg-slate-50/60 dark:bg-slate-950">
                {{-- Dibaca sidebar (persist) setelah tiap navigasi untuk menyorot menu aktif --}}
                <span id="sidebar-state" hidden
                      data-active="{{ json_encode($sidebarActive) }}"
                      data-settings="{{ $settingsActive ? 1 : 0 }}"></span>
                <x-alert />
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Dialog konfirmasi global, pengganti confirm()/wire:confirm bawaan
         browser di seluruh halaman Unit Admin -- lihat komentar lengkap di
         components/confirm-dialog.blade.php --}}
    <x-confirm-dialog />

    @livewireScripts
</body>

{{-- ApexCharts hanya dimuat oleh halaman yang benar-benar memakai grafik
(lewat @push('apexcharts') di view masing-masing), bukan di semua halaman. --}}
@stack('apexcharts')
</html>