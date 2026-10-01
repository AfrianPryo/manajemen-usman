{{--
    Shell halaman auth (login & ganti password): satu kartu di tengah layar.

    Pemakaian:
        <x-auth-card title="Judul">
            <x-slot:subtitle>Teks penjelas singkat</x-slot:subtitle>   (opsional)
            <x-slot:progress>indikator langkah</x-slot:progress>       (opsional)
            ...isi form...
            <x-slot:below>tautan kecil di bawah kartu</x-slot:below>   (opsional)
        </x-auth-card>

    Catatan: kartu SENGAJA tanpa overflow-hidden supaya dropdown negara pada
    <x-phone-input> tidak terpotong. Logo bawaan berupa pasangan light/dark (lihat brand-logo).
--}}
@props(['title'])

<div class="flex min-h-screen w-full flex-col items-center justify-center bg-slate-50 px-4 py-10 font-sans text-neutral-800 selection:bg-blue-900 selection:text-white dark:bg-slate-950 dark:text-neutral-100">

    <div class="w-full max-w-[26rem] rounded-md border border-neutral-200 bg-white px-6 py-8 sm:px-9 sm:py-10 dark:border-slate-800 dark:bg-slate-900">

        <div class="flex justify-center">
            <x-brand-logo variant="card" />
        </div>

        <div class="mt-7 text-center">
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 sm:text-2xl dark:text-white">{{ $title }}</h1>

            @isset($subtitle)
                <p class="mx-auto mt-1.5 max-w-xs text-xs leading-relaxed text-neutral-500 dark:text-neutral-400">{{ $subtitle }}</p>
            @endisset

            @isset($progress)
                <div class="mt-4">{{ $progress }}</div>
            @endisset
        </div>

        <div class="mt-8">
            {{ $slot }}
        </div>
    </div>

    @isset($below)
        {{ $below }}
    @endisset

    <style>
        /* Menghilangkan background autofill browser */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-transition: "color 9999s ease-out, background-color 9999s ease-out";
            -webkit-transition-delay: 9999s;
            -webkit-text-fill-color: inherit !important;
            caret-color: currentColor;
        }
        .dark input:-webkit-autofill,
        .dark input:-webkit-autofill:hover,
        .dark input:-webkit-autofill:focus,
        .dark input:-webkit-autofill:active {
            -webkit-text-fill-color: #ffffff !important;
            caret-color: #ffffff;
        }
    </style>
</div>