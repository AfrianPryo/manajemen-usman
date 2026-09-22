<div class="min-h-screen w-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 flex font-sans selection:bg-blue-900 selection:text-white overflow-hidden relative">

    {{-- Sisi Kiri: Branding --}}
    <div class="hidden md:flex md:w-1/2 lg:w-[55%] relative flex-col justify-between p-8 lg:p-12 select-none overflow-hidden bg-blue-900 dark:bg-blue-950">

        {{-- Dekorasi grid halus (pengganti garis lengkung dekoratif) --}}
        <svg class="absolute inset-0 z-0 h-full w-full pointer-events-none opacity-[0.08]" aria-hidden="true">
            <defs>
                <pattern id="cp-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="#ffffff" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#cp-grid)" />
        </svg>

        {{-- Logo (mengikuti aset logo yang sudah ada di halaman login) --}}
        <div class="relative z-10 flex items-center gap-2">
            {{-- Logo untuk mode terang --}}
            <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto hidden dark:block">

            {{-- Logo untuk mode gelap --}}
            <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto block dark:hidden">
        </div>

        {{-- Teks Bawah --}}
        <div class="relative z-10 max-w-sm">
            <p class="text-sm text-white/70 font-medium leading-relaxed tracking-tight">
                Perbarui password Anda secara berkala untuk menjaga keamanan akun dan seluruh data operasional Anda.
            </p>
        </div>
    </div>

    {{-- Sisi Kanan: FORM GANTI PASSWORD --}}
    <div class="w-full md:w-1/2 lg:w-[45%] flex flex-col p-6 sm:p-10 lg:p-12">

        {{-- Nav Atas --}}
        <div class="flex justify-start items-center mb-8">
            @if (!auth()->user()?->must_change_password)
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors duration-150" aria-label="Kembali ke Dashboard">
                    <x-heroicon-o-chevron-left class="h-3.5 w-3.5" stroke-width="2" />
                    Dashboard
                </a>
            @endif
        </div>

        {{-- Konten Tengah --}}
        <div class="flex-1 flex flex-col justify-center w-full max-w-md mx-auto lg:mx-0">

            {{-- Judul --}}
            <div class="mb-7">
                @if ($needsPhoneSetup)
                    <p class="text-[11px] font-bold text-blue-800 dark:text-sky-400 uppercase tracking-wide mb-1.5">
                        Langkah {{ $step }} dari 2
                    </p>
                @endif

                <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">
                    {{ $step === 2 ? 'Setup Nomor WA Aktif' : 'Ganti Password' }}
                </h1>
                <p class="mt-1.5 text-xs text-neutral-400">
                    @if ($step === 2)
                        Nomor ini dipakai untuk menerima notifikasi & kode OTP penting terkait akun Anda.
                    @elseif (auth()->user()?->must_change_password)
                        Anda diwajibkan untuk mengganti password sebelum melanjutkan.
                    @else
                        Perbarui password Anda secara berkala untuk menjaga keamanan akun.
                    @endif
                </p>

                @if ($needsPhoneSetup)
                    {{-- Indikator langkah: pola dot progress ini sama persis dengan
                         yang dipakai modal onboarding di dashboard, supaya konsisten. --}}
                    <div class="flex items-center gap-1.5 mt-4">
                        <span class="h-1.5 rounded-full transition-all {{ $step === 1 ? 'w-5 bg-blue-900 dark:bg-sky-400' : 'w-1.5 bg-neutral-200 dark:bg-slate-600' }}"></span>
                        <span class="h-1.5 rounded-full transition-all {{ $step === 2 ? 'w-5 bg-blue-900 dark:bg-sky-400' : 'w-1.5 bg-neutral-200 dark:bg-slate-600' }}"></span>
                    </div>
                @endif
            </div>

            {{-- Alert Success --}}
            @if (session()->has('message'))
                <div class="mb-6 p-3 text-xs text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/30 rounded-sm" role="alert">
                    <span class="font-medium">{{ session('message') }}</span>
                </div>
            @endif

            {{-- Alert Error --}}
            @if (session()->has('error'))
                <div class="mb-6 p-3 text-xs text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 rounded-sm" role="alert">
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Form Utama --}}
            <form
                wire:submit="{{ $needsPhoneSetup && $step === 1 ? 'nextStep' : 'update' }}"
                id="changePasswordForm"
                class="space-y-6"
            >
                @if ($step === 1)
                <div class="grid grid-cols-1 gap-5">

                    {{-- Password Saat Ini --}}
                    <div x-data="{ show: false }" class="relative">
                        <label for="current_password" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Password Saat Ini
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-lock-closed class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="current_password"
                                id="current_password"
                                :type="show ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-9 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="Masukkan password saat ini"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 focus:outline-none"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
                                <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                            </button>
                        </div>
                        @error('current_password') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password Baru --}}
                    <div x-data="{ show: false }" class="relative">
                        <label for="new_password" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Password Baru
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-lock-closed class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="new_password"
                                id="new_password"
                                :type="show ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-9 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="Minimal 8 karakter (huruf besar, kecil, angka)"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 focus:outline-none"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
                                <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                            </button>
                        </div>
                        @error('new_password') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Konfirmasi Password Baru --}}
                    <div x-data="{ show: false }" class="relative">
                        <label for="new_password_confirmation" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Konfirmasi Password Baru
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-lock-closed class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="new_password_confirmation"
                                id="new_password_confirmation"
                                :type="show ? 'text' : 'password'"
                                autocomplete="new-password"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-9 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="Ulangi password baru"
                            >
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute right-3 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 focus:outline-none"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
                                <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                            </button>
                        </div>
                        @error('new_password_confirmation') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>
                @endif

                @if ($step === 2)
                <div class="grid grid-cols-1 gap-5">

                    {{-- Nomor WA Aktif --}}
                    <div class="relative">
                        <label for="phone" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Nomor WhatsApp Aktif
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-phone class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="phone"
                                id="phone"
                                type="text"
                                inputmode="numeric"
                                autocomplete="tel"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-3 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="08xxxxxxxxxx"
                            >
                        </div>
                        <p class="mt-2 text-[11px] text-neutral-400">Dipakai untuk notifikasi & kode OTP penting, misalnya saat ganti password mendatang.</p>
                        @error('phone') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>
                @endif
            </form>

            {{-- Tombol Submit --}}
            @php
                $submitTarget = $needsPhoneSetup && $step === 1 ? 'nextStep' : 'update';
                $submitLabel  = $needsPhoneSetup && $step === 1 ? 'Lanjutkan' : 'Simpan Password';
            @endphp
            <div class="mt-8 flex justify-between items-center">
                @if ($step === 2)
                    <button
                        type="button"
                        wire:click="backStep"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <x-heroicon-o-chevron-left class="w-3.5 h-3.5" stroke-width="2" />
                        Kembali
                    </button>
                @else
                    <span></span>
                @endif

                <button
                    form="changePasswordForm"
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="{{ $submitTarget }}">{{ $submitLabel }}</span>
                    <span wire:loading wire:target="{{ $submitTarget }}">Memproses...</span>

                    <x-heroicon-o-arrow-right wire:loading.remove wire:target="{{ $submitTarget }}" class="w-3.5 h-3.5" stroke-width="2.5" />
                    <svg wire:loading wire:target="{{ $submitTarget }}" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

        </div>

    </div>
</div>

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