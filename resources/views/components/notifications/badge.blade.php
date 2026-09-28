{{--
    Badge tipe notifikasi. Pola warna sama dengan badge di halaman lain
    (rounded-full + border, lihat Pengumuman); nada ditentukan dari teks
    badge yang dikirim SystemNotification.
--}}
@props(['label'])

@php
    $tones = [
        'rose'    => 'bg-rose-100 text-rose-700 border-rose-200 dark:bg-rose-950 dark:text-rose-400 dark:border-rose-900',
        'amber'   => 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-950 dark:text-amber-400 dark:border-amber-900',
        'violet'  => 'bg-violet-100 text-violet-700 border-violet-200 dark:bg-violet-950 dark:text-violet-400 dark:border-violet-900',
        'emerald' => 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-950 dark:text-emerald-400 dark:border-emerald-900',
        'sky'     => 'bg-sky-100 text-sky-700 border-sky-200 dark:bg-sky-950 dark:text-sky-400 dark:border-sky-900',
    ];

    $tone = match (mb_strtolower(trim((string) $label))) {
        'stok habis', 'rusak'                                     => 'rose',
        'stok menipis', 'jatuh tempo', 'pending', 'maintenance'   => 'amber',
        'reset password'                                          => 'violet',
        'otomatis'                                                => 'emerald',
        default                                                   => 'sky',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-block shrink-0 whitespace-nowrap text-[10px] font-medium px-2 py-0.5 rounded-full border ' . $tones[$tone]]) }}>{{ $label }}</span>
