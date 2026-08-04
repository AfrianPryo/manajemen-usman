<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Login' }} - SIMS.Usaha</title>

    {{-- Load Vite & Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- Load Livewire Styles --}}
    @livewireStyles
</head>
<body class="bg-slate-50 dark:bg-slate-950 font-sans antialiased">
    
    {{-- Ini akan digantikan oleh isi dari login.blade.php --}}
    {{ $slot }}

    {{-- Load Livewire Scripts --}}
    @livewireScripts
</body>
</html>