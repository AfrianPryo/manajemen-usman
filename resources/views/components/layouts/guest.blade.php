<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Judul & ikon tab mengikuti identitas yang diatur Master Admin di Pengaturan
         (Setting 'app_name' / 'app_logo'), sama seperti layout dashboard & landing.
         Fallback ke identitas bawaan selama admin belum mengatur apa pun. --}}
    @php
        $__guestAppName = \App\Models\Setting::get('app_name') ?: 'SIMS.Usaha';
        $__guestFavicon = \App\Models\Setting::get('app_logo');
    @endphp
    @if ($__guestFavicon)
        <link rel="icon" href="{{ asset('storage/' . $__guestFavicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    <title>{{ $title ?? 'Login' }} - {{ $__guestAppName }}</title>

    <script>
        // Samakan dengan preferensi tema yang dipilih user di dashboard
        // (lihat components/layouts/app.blade.php & unit.blade.php), supaya
        // halaman login pun konsisten dan tidak "kedip" mode terang dulu.
        (function () {
            try {
                const stored = localStorage.getItem('theme');
                const isDark = stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', isDark);
            } catch (e) {}
        })();
    </script>

    {{-- Load Vite & Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Load Livewire Styles --}}
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans antialiased">
    
    {{-- Panel popup toast global (pengganti flash message inline) --}}
    <x-alert />

    {{-- Ini akan digantikan oleh isi dari login.blade.php --}}
    {{ $slot }}

    {{-- Load Livewire Scripts --}}
    @livewireScripts
</body>
</html>