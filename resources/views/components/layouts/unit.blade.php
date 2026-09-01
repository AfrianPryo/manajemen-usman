<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard Unit' }} - Usaha Mandiri Sekolah</title>
    <style>
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
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden font-sans"
         x-data="{
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            mobileSidebarOpen: false
         }"
         x-init="$watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value))"
         @keydown.escape.window="mobileSidebarOpen = false">

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
             class="fixed inset-0 z-40 bg-slate-900/50 md:hidden"
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
            class="fixed inset-y-0 left-0 z-50 w-64 md:relative md:z-auto bg-white border-r border-slate-200/70 text-slate-700 flex-shrink-0 flex flex-col justify-between select-none overflow-hidden">

            {{-- Logo Header (fixed, TIDAK ikut ter-scroll) --}}
            @php
                $appName = \App\Models\Setting::get('app_name', 'USMAN - Usaha Mandiri Sekolah');
            @endphp
            <div class="h-12 flex items-center justify-between px-4 font-bold text-sm text-slate-900 border-b border-slate-100 shrink-0 tracking-tight">
                <span class="flex items-center gap-2 overflow-hidden">
                    <span class="h-6 w-6 rounded-full bg-slate-900 text-white flex items-center justify-center font-extrabold text-[11px] shadow-xs shrink-0">{{ strtoupper(substr($appName, 0, 1)) }}</span>
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
                    class="hidden md:inline-flex shrink-0 p-1 rounded-md text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none">
                    <x-heroicon-o-chevron-double-left class="w-3.5 h-3.5" />
                </button>

                {{-- Tombol Tutup Sidebar (mobile) --}}
                <button
                    @click="mobileSidebarOpen = false"
                    type="button"
                    title="Tutup menu"
                    class="md:hidden shrink-0 p-1 rounded-md text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>

            {{-- Tombol Expand saat collapsed (fixed, tampil di bawah logo, terpusat, desktop only) --}}
            <div x-show="sidebarCollapsed" x-cloak class="hidden md:flex justify-center py-1.5 border-b border-slate-100 shrink-0">
                <button
                    @click="sidebarCollapsed = !sidebarCollapsed"
                    type="button"
                    title="Perluas sidebar"
                    class="p-1 rounded-md text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-colors focus:outline-none">
                    <x-heroicon-o-chevron-double-right class="w-3.5 h-3.5" />
                </button>
            </div>

            {{-- Area menu yang bisa discroll (logo & footer tetap diam) --}}
            <div class="no-scrollbar flex-1 overflow-y-auto overflow-x-hidden">
                @php
                    $slugUnitAktif = request()->route('unit')?->slug ?? auth()->user()?->unit?->slug;

                    // Kategori unit yang SEDANG DIBUKA (bukan kategori unit
                    // milik user login) -- dipakai untuk menyaring item menu
                    // yang punya key 'unit_category' (lihat config/menu.php,
                    // grup "Manajemen Layanan"). Konsisten dengan pola
                    // $slugUnitAktif di atas: kalau route-nya sedang membuka
                    // unit tertentu (Master Admin memantau unit lain), pakai
                    // kategori unit ITU, bukan kategori unit user login.
                    $categoryUnitAktif = request()->route('unit')?->category ?? auth()->user()?->unit?->category;
                @endphp
                <nav class="py-4 px-2.5 space-y-0.5" @click="mobileSidebarOpen = false">
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
                                             x-cloak class="px-2.5 pb-1 text-[9px] font-bold text-slate-400 tracking-wider uppercase">
                                            {{ $item['label'] }}
                                        </div>

                                        {{-- Sub Menu Items --}}
                                        <div class="space-y-0.5">
                                            @foreach($filteredChildren as $child)
                                                @continue(!is_null($child['roles']) && !auth()->user()?->hasAnyRole($child['roles']) && !auth()->user()?->isMasterAdmin())
                                                @php
                                                    $childActive = request()->routeIs($child['route'].'*');
                                                    // Semua item di sidebar ini sudah dipastikan berawalan
                                                    // "unit." (lihat filter di atas). Slug diambil dari unit
                                                    // yang SEDANG DIBUKA (route-model-binding {unit:slug}),
                                                    // bukan dari unit milik user login — supaya link tetap
                                                    // benar saat halaman ini dibuka Master Admin yang sedang
                                                    // memantau unit lain.
                                                    $childRouteParams = ['unit' => $slugUnitAktif];
                                                @endphp
                                                <a href="{{ route($child['route'], $childRouteParams) }}"
                                                   title="{{ $child['label'] }}"
                                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs font-medium transition-colors duration-150 {{ $childActive ? 'bg-slate-100 text-slate-900 font-semibold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                        @if(isset($child['icon']))
                                                            <span class="w-5 h-5 flex items-center justify-center shrink-0 {{ $childActive ? 'text-slate-900' : 'text-slate-400' }}">
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
                                    $itemActive = request()->routeIs($item['route'].'*');
                                    $itemRouteParams = ['unit' => $slugUnitAktif];
                                @endphp
                                <a href="{{ route($item['route'], $itemRouteParams) }}"
                                   title="{{ $item['label'] }}"
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors duration-150 {{ $itemActive ? 'bg-slate-100 text-slate-900 font-semibold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        @if(isset($item['icon']))
                                            <span class="w-5 h-5 flex items-center justify-center shrink-0 {{ $itemActive ? 'text-slate-900' : 'text-slate-400' }}">
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
                                         x-cloak class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Bottom User Profile & Pop-up Menu Profil Saya --}}
            @php
                // Foto profil admin master (diatur di Pengaturan Sistem > Profil Admin)
                $sidebarPhotoPath = auth()->user()->profile_photo_path ?? null;
                $sidebarPhotoUrl  = $sidebarPhotoPath ? asset('storage/' . $sidebarPhotoPath) : null;
                $sidebarInitial   = strtoupper(substr(auth()->user()->name ?? 'U', 0, 1));
            @endphp
            <div class="p-2 border-t border-slate-100 relative" x-data="{ userMenuOpen: false }">
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
                    class="absolute bottom-full mb-1.5 bg-white border border-slate-200/90 rounded-xl shadow-xl p-1.5 z-50 text-slate-800">

                    {{-- ====== MODE COLLAPSED: hanya ikon ====== --}}
                    <template x-if="sidebarCollapsed && !mobileSidebarOpen">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80 flex items-center justify-center font-bold text-[11px] shrink-0 overflow-hidden mb-0.5"
                                 title="{{ auth()->user()->name }}">
                                @if ($sidebarPhotoUrl)
                                    <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                                @else
                                    {{ $sidebarInitial }}
                                @endif
                            </div>

                            <div class="w-6 h-px bg-slate-100"></div>

                            {{-- Link Profil Saya (ikon saja) --}}
                            @php
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
                                $isActive = request()->routeIs('unit.profile.*');
                            @endphp
                            <a href="{{ $settingsUrl }}"
                               @click="userMenuOpen = false; mobileSidebarOpen = false"
                               title="{{ $settingsLabel }}"
                               class="flex items-center justify-center w-8 h-8 rounded-lg transition-all {{ $isActive ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                                <x-heroicon-o-user-circle class="w-4 h-4" />
                            </a>

                            {{-- Tombol Logout (ikon saja) --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" title="Log out"
                                        class="flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:bg-rose-50 hover:text-rose-700 transition-all">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                    </template>

                    {{-- ====== MODE EXPANDED: kartu lengkap (perilaku lama) ====== --}}
                    <template x-if="!(sidebarCollapsed && !mobileSidebarOpen)">
                        <div>
                            {{-- Detail User --}}
                            <div class="flex items-center gap-2.5 p-2 border-b border-slate-100 mb-1">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden">
                                    @if ($sidebarPhotoUrl)
                                        <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                                    @else
                                        {{ $sidebarInitial }}
                                    @endif
                                </div>
                                <div class="overflow-hidden">
                                    <p class="text-xs font-bold text-slate-900 truncate leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-[10px] text-slate-500 truncate leading-tight">{{ auth()->user()->email ?? auth()->user()->getRoleNames()->first() }}</p>
                                </div>
                            </div>

                            {{-- Link Profil Saya --}}
                            @php
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
                                $isActive = request()->routeIs('unit.profile.*');
                            @endphp

                            <a href="{{ $settingsUrl }}"
                            @click="userMenuOpen = false; mobileSidebarOpen = false"
                            class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ $isActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-medium' }}">
                                <x-heroicon-o-user-circle class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-400' }}" />
                                <span>{{ $settingsLabel }}</span>
                            </a>

                            {{-- Tombol Logout --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-slate-600 hover:bg-rose-50 hover:text-rose-700 transition-all text-left font-medium group">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-600" />
                                    <span>Log out</span>
                                </button>
                            </form>
                        </div>
                    </template>
                </div>

                {{-- Trigger Button --}}
                <button @click="userMenuOpen = !userMenuOpen"
                        title="{{ auth()->user()->name }}"
                        class="w-full flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition-colors text-left focus:outline-none border border-transparent hover:border-slate-200/60">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-6.5 h-6.5 rounded-md bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs overflow-hidden">
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
                              x-cloak class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</span>
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
                        class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                </button>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-[#f8f9fa] min-w-0">

            {{-- HEADER STYLE REFERENSI (BREADCRUMB HEADER) --}}
            <header class="h-12 bg-white border-b border-slate-200/70 flex items-center justify-between px-3 md:px-6 shrink-0 gap-2">

                {{-- Left: Hamburger (mobile) + Path Breadcrumb --}}
                <div class="flex items-center gap-2 min-w-0">

                    {{-- Tombol Buka Sidebar (mobile only) --}}
                    <button
                        @click="mobileSidebarOpen = true"
                        type="button"
                        title="Buka menu"
                        class="md:hidden shrink-0 p-1.5 -ml-1 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors focus:outline-none">
                        <x-heroicon-o-bars-3 class="w-5 h-5" />
                    </button>

                    <div class="flex items-center gap-2 text-xs font-medium text-slate-500 min-w-0">

                        {{-- Parent / Kategori --}}
                        <div class="hidden sm:flex items-center gap-1.5 px-2 py-1 text-slate-700 font-semibold shrink-0">
                            <x-heroicon-o-building-storefront class="w-3.5 h-3.5 text-slate-500" />
                            <span>{{ $category ?? 'Unit Usaha' }}</span>
                        </div>

                        {{-- Separator Slash --}}
                        <span class="hidden sm:inline text-slate-300 font-normal">/</span>

                        {{-- Current Page Title --}}
                        <div class="flex items-center gap-1.5 text-slate-900 font-bold min-w-0">
                            <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5 text-slate-700 shrink-0" />
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
                        <a href="{{ route('master.dashboard') }}"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-neutral-600 dark:text-neutral-300 bg-neutral-50 dark:bg-slate-900/40 border border-neutral-200 dark:border-slate-700 rounded-[3px] hover:bg-neutral-100 dark:hover:bg-slate-900 transition-all shadow-sm shadow-black/[0.02]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                            <span class="hidden sm:inline">Kembali ke Master</span>
                        </a>
                    @endif

                </div>
            </header>

            {{-- Main Scroll Content Area --}}
            <main class="no-scrollbar flex-1 overflow-y-auto p-2 bg-slate-50/60">
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

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</html>