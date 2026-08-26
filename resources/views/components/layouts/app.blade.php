<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Usaha Mandiri Sekolah</title>
    <style>
        #main-content:not(.is-ready) {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden font-sans">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r border-slate-200/70 text-slate-700 flex-shrink-0 hidden md:flex md:flex-col justify-between select-none">
            <div class="flex flex-col flex-1 overflow-y-auto">
                
                {{-- Logo Header --}}
                <div class="h-12 flex items-center px-4 font-bold text-sm text-slate-900 border-b border-slate-100 shrink-0 tracking-tight">
                    <span class="flex items-center gap-2">
                        <span class="h-6 w-6 rounded-full bg-slate-900 text-white flex items-center justify-center font-extrabold text-[11px] shadow-xs">U</span>
                        UMS Sekolah
                    </span>
                </div>

                {{-- Navigation Menu Utama --}}
                <nav class="flex-1 py-4 px-2.5 space-y-0.5">
                    @foreach(config('menu') as $item)
                        @php
                            $label = strtolower($item['label'] ?? '');
                            $route = strtolower($item['route'] ?? '');
                            $isSettings = str_contains($label, 'pengaturan') || str_contains($label, 'setting') || str_contains($route, 'settings');
                        @endphp

                        @continue($isSettings)

                        @php
                            $canSee = is_null($item['roles']) || auth()->user()?->hasAnyRole($item['roles']);
                        @endphp

                        @if($canSee)
                            @if(isset($item['children']))
                                @php
                                    $filteredChildren = collect($item['children'])->reject(function($child) {
                                        $cLabel = strtolower($child['label'] ?? '');
                                        $cRoute = strtolower($child['route'] ?? '');
                                        return str_contains($cLabel, 'pengaturan') || str_contains($cLabel, 'setting') || str_contains($cRoute, 'settings');
                                    });
                                @endphp

                                @if($filteredChildren->count() > 0)
                                    <div class="pt-1.5 pb-0.5 first:pt-0">
                                        {{-- Header Kategori --}}
                                        <div class="px-2.5 pb-1 text-[9px] font-bold text-slate-400 tracking-wider uppercase">
                                            {{ $item['label'] }}
                                        </div>

                                        {{-- Sub Menu Items --}}
                                        <div class="space-y-0.5">
                                            @foreach($filteredChildren as $child)
                                                @continue(!is_null($child['roles']) && !auth()->user()?->hasAnyRole($child['roles']))
                                                @php
                                                    $childActive = request()->routeIs($child['route'].'*');
                                                @endphp
                                                <a href="{{ route($child['route']) }}"
                                                class="flex items-center justify-between px-2.5 py-1.5 rounded-md text-xs font-medium transition-all duration-150 {{ $childActive ? 'bg-slate-100 text-slate-900 font-semibold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                                    <div class="flex items-center gap-2 overflow-hidden">
                                                        @if(isset($child['icon']))
                                                            <span class="{{ $childActive ? 'text-slate-900' : 'text-slate-400' }}">
                                                                {!! $child['icon'] !!}
                                                            </span>
                                                        @endif
                                                        <span class="truncate">{{ $child['label'] }}</span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                {{-- Single Menu Item --}}
                                @php
                                    $itemActive = request()->routeIs($item['route'].'*');
                                @endphp
                                <a href="{{ route($item['route']) }}"
                                class="flex items-center justify-between px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $itemActive ? 'bg-slate-100 text-slate-900 font-semibold shadow-2xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                                    <div class="flex items-center gap-2 overflow-hidden">
                                        @if(isset($item['icon']))
                                            <span class="{{ $itemActive ? 'text-slate-900' : 'text-slate-400' }}">
                                                {!! $item['icon'] !!}
                                            </span>
                                        @endif
                                        <span class="truncate">{{ $item['label'] }}</span>
                                    </div>
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Bottom User Profile & Pop-up Menu Pengaturan --}}
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
                    class="absolute bottom-full left-2 right-2 mb-1.5 bg-white border border-slate-200/90 rounded-xl shadow-xl p-1.5 z-50 text-slate-800">
                    
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

                    {{-- Link Pengaturan Sistem --}}
                    @php
                        $hasSettingsRoute = Route::has('master.settings.index');
                        $settingsUrl = $hasSettingsRoute ? route('master.settings.index') : '#';
                        $isActive = request()->routeIs('master.settings.*');
                    @endphp

                    <a href="{{ $settingsUrl }}" 
                    @click="userMenuOpen = false"
                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs transition-all {{ $isActive ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100/80 hover:text-slate-900 font-medium' }}">
                        <svg class="w-3.5 h-3.5 {{ $isActive ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Pengaturan Sistem</span>
                    </a>

                    {{-- Tombol Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-slate-600 hover:bg-rose-50 hover:text-rose-700 transition-all text-left font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Log out</span>
                        </button>
                    </form>
                </div>

                {{-- Trigger Button --}}
                <button @click="userMenuOpen = !userMenuOpen" 
                        class="w-full flex items-center justify-between p-1.5 rounded-lg hover:bg-slate-50 transition-colors text-left focus:outline-none border border-transparent hover:border-slate-200/60">
                    <div class="flex items-center gap-2 overflow-hidden">
                        <div class="w-6.5 h-6.5 rounded-md bg-slate-900 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs overflow-hidden">
                            @if ($sidebarPhotoUrl)
                                <img src="{{ $sidebarPhotoUrl }}" alt="Foto profil" class="w-full h-full object-cover">
                            @else
                                {{ $sidebarInitial }}
                            @endif
                        </div>
                        <span class="text-xs font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden bg-[#f8f9fa]">

            {{-- HEADER STYLE REFERENSI (BREADCRUMB HEADER) --}}
            <header class="h-14 bg-white border-b border-slate-200/70 flex items-center justify-between px-6 shrink-0">
                
                {{-- Left: Path Breadcrumb --}}
                <div class="flex items-center gap-2 text-xs font-medium text-slate-500">
                    
                    {{-- Parent / Kategori --}}
                    <div class="flex items-center gap-1.5 px-2 py-1 text-slate-700 font-semibold">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span>{{ $category ?? 'Master' }}</span>
                    </div>

                    {{-- Separator Slash --}}
                    <span class="text-slate-300 font-normal">/</span>

                    {{-- Current Page Title --}}
                    <div class="flex items-center gap-1.5 text-slate-900 font-bold">
                        <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>{{ $title ?? 'Dashboard' }}</span>
                    </div>
                </div>

                {{-- Right: Light/Dark Mode, Notification Component & User Role Badge --}}
                <div class="flex items-center gap-3">

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
                            <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                            </svg>

                            {{-- Icon Sun --}}
                            <svg x-show="darkMode" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- PEMANGGILAN KOMPONEN LIVEWIRE --}}
                    <livewire:notification-sidebar 
                        :role="$role ?? 'master'"
                        :view-all-url="$viewAllUrl ?? '#'"
                    />

                </div>
            </header>

            {{-- Main Scroll Content Area --}}
            <main class="flex-1 overflow-y-auto p-2 bg-slate-50/60">
                <x-alert />
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</html>