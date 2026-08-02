<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMS - Sistem Manajemen Usaha Mandiri Sekolah')</title>
    <meta name="description" content="Portal terpadu untuk mengelola seluruh unit usaha mandiri sekolah: TEFA, Bengkel, FotoCopy, Alfamart Mini, Teh Siswa, dan Bank Sekolah.">

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
<body class="bg-slate-50 dark:bg-slate-950 font-sans text-slate-700 dark:text-slate-300 antialiased transition-colors duration-300">

    @yield('content')

    @stack('scripts')
</body>
</html>