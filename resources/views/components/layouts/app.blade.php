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
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        <aside class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 hidden md:flex md:flex-col justify-between">
            <div class="flex flex-col flex-1 overflow-y-auto">
                {{-- Logo Header --}}
                <div class="h-16 flex items-center px-6 font-bold text-lg text-white border-b border-slate-800 flex-shrink-0">
                    UMS Sekolah
                </div>

                {{-- Navigation Menu Utama --}}
                <nav class="flex-1 py-4 px-3 space-y-1">
                    @foreach(config('menu') as $item)
                        @php
                            $label = strtolower($item['label'] ?? '');
                            $route = strtolower($item['route'] ?? '');
                            
                            // Abaikan menu jika mengandung kata pengaturan/settings agar tidak muncul di tengah
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
                                    <div x-data="{ open: {{ request()->routeIs($filteredChildren->pluck('route')->map(fn($r) => $r.'*')->toArray()) ? 'true' : 'false' }} }">
                                        <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-sm font-medium">
                                            <span>{{ $item['label'] }}</span>
                                            <svg :class="open ? 'rotate-90' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </button>
                                        <div x-show="open" x-transition class="ml-3 mt-1 space-y-1">
                                            @foreach($filteredChildren as $child)
                                                @continue(!is_null($child['roles']) && !auth()->user()?->hasAnyRole($child['roles']))
                                                <a href="{{ route($child['route']) }}"
                                                   class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs($child['route'].'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                                    {{ $child['label'] }}
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @else
                                <a href="{{ route($item['route']) }}"
                                   class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs($item['route'].'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                    {{ $item['label'] }}
                                </a>
                            @endif
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Bottom User Profile & Pop-up Menu Pengaturan --}}
            <div class="p-3 border-t border-slate-800 relative" x-data="{ userMenuOpen: false }">
                {{-- Pop-up Menu Floating Upward --}}
                <div x-show="userMenuOpen" 
                     @click.outside="userMenuOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                     x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                     class="absolute bottom-full left-3 right-3 mb-2 bg-slate-800 border border-slate-700/80 rounded-xl shadow-xl p-2 z-50 text-slate-200">
                    
                    {{-- Detail User --}}
                    <div class="flex items-center gap-3 p-2.5 border-b border-slate-700/60 mb-1">
                        <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? auth()->user()->getRoleNames()->first() }}</p>
                        </div>
                    </div>

                    {{-- Link Pengaturan Sistem (Mengarah ke master.settings.index) --}}
                    @php
                        $hasSettingsRoute = Route::has('master.settings.index');
                        $settingsUrl = $hasSettingsRoute ? route('master.settings.index') : '#';
                        $isActive = request()->routeIs('master.settings.*');
                    @endphp

                    <a href="{{ $settingsUrl }}" 
                       @click="userMenuOpen = false"
                       class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition {{ $isActive ? 'bg-indigo-600 text-white font-medium' : 'text-slate-300 hover:bg-slate-700/60 hover:text-white' }}">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Pengaturan Sistem</span>
                    </a>

                    {{-- Tombol Logout --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-300 hover:bg-slate-700/60 hover:text-white transition text-left">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Log out</span>
                        </button>
                    </form>
                </div>

                {{-- Trigger Button --}}
                <button @click="userMenuOpen = !userMenuOpen" 
                        class="w-full flex items-center justify-between p-2 rounded-xl hover:bg-slate-800 transition text-left focus:outline-none">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-sm font-medium text-slate-200 truncate">{{ auth()->user()->name }}</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/>
                    </svg>
                </button>
            </div>
        </aside>

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                <div class="text-sm text-gray-600">
                    <span class="text-xs bg-indigo-50 text-indigo-700 font-medium px-2.5 py-1 rounded-full border border-indigo-200">
                        {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
                    </span>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                <x-alert />
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>