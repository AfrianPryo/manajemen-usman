<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - Usaha Mandiri Sekolah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        <aside class="w-64 bg-slate-900 text-slate-200 flex-shrink-0 hidden md:flex md:flex-col">
            <div class="h-16 flex items-center px-6 font-bold text-lg text-white border-b border-slate-800">
                UMS Sekolah
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                @foreach(config('menu') as $item)
                    @php
                        $canSee = is_null($item['roles']) || auth()->user()?->hasAnyRole($item['roles']);
                    @endphp
                    @if($canSee)
                        @if(isset($item['children']))
                            <div x-data="{ open: {{ request()->routeIs(collect($item['children'])->pluck('route')->map(fn($r) => $r.'*')->toArray()) ? 'true' : 'false' }} }">
                                <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-lg hover:bg-slate-800 text-sm font-medium">
                                    <span>{{ $item['label'] }}</span>
                                    <svg :class="open ? 'rotate-90' : ''" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <div x-show="open" x-transition class="ml-3 mt-1 space-y-1">
                                    @foreach($item['children'] as $child)
                                        @continue(!is_null($child['roles']) && !auth()->user()?->hasAnyRole($child['roles']))
                                        <a href="{{ route($child['route']) }}"
                                           class="block px-3 py-2 rounded-lg text-sm {{ request()->routeIs($child['route'].'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                            {{ $child['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route($item['route']) }}"
                               class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs($item['route'].'*') ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 text-slate-300' }}">
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endif
                @endforeach
            </nav>
            <div class="p-4 border-t border-slate-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-full text-left text-sm text-slate-300 hover:text-white">Keluar</button>
                </form>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="h-16 bg-white border-b flex items-center justify-between px-6">
                <h1 class="text-lg font-semibold text-gray-800">{{ $title ?? 'Dashboard' }}</h1>
                <div class="text-sm text-gray-600">
                    {{ auth()->user()->name }}
                    <span class="ml-1 text-xs bg-gray-100 px-2 py-0.5 rounded-full">{{ auth()->user()->getRoleNames()->first() ?? '-' }}</span>
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
