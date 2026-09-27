<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        // Judul & favicon landing page mengikuti identitas yang diatur admin
        // master di menu Pengaturan > Fitur & Modul (Setting 'app_name' /
        // 'app_logo'), sama seperti sidebar dashboard. Fallback ke identitas
        // bawaan landing page ("SIMS") selama admin belum mengatur apa pun.
        $__landingAppName = \App\Models\Setting::get('app_name') ?: 'SIMS';
        $__landingFavicon = \App\Models\Setting::get('app_logo');
    @endphp
    <title>@yield('title', $__landingAppName . ' - Portal Usaha Mandiri Sekolah')</title>
    <meta name="description" content="Portal terpadu untuk mengelola seluruh unit usaha mandiri sekolah: TEFA, Bengkel, FotoCopy, Alfamart Mini, Teh Siswa, dan Bank Sekolah.">
    @if ($__landingFavicon)
        <link rel="icon" href="{{ asset('storage/' . $__landingFavicon) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    @endif

    {{-- Cegah flash tema salah saat reload (dijalankan sebelum CSS/JS lain) --}}
    <script>
        (function () {
            const stored = localStorage.getItem('sims-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (!stored && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    {{-- Manrope — grotesque, dipakai untuk heading dan body --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body data-page="landing" class="bg-slate-50 dark:bg-slate-950 font-sans text-slate-700 dark:text-slate-300 antialiased transition-colors duration-300">

    @yield('content')

    @stack('scripts')
</body>
</html>