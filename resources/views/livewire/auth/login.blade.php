<div class="min-h-screen w-full bg-white dark:bg-slate-900 text-neutral-800 dark:text-neutral-100 flex font-sans selection:bg-blue-900 selection:text-white overflow-hidden relative">

    {{-- Sisi Kiri: Branding --}}
    <div class="hidden md:flex md:w-1/2 lg:w-[55%] relative flex-col justify-between p-8 lg:p-12 select-none overflow-hidden bg-blue-900 dark:bg-blue-950">

        {{-- Dekorasi grid halus (pengganti garis lengkung dekoratif) --}}
        <svg class="absolute inset-0 z-0 h-full w-full pointer-events-none opacity-[0.08]" aria-hidden="true">
            <defs>
                <pattern id="login-grid" width="32" height="32" patternUnits="userSpaceOnUse">
                    <path d="M 32 0 L 0 0 0 32" fill="none" stroke="#ffffff" stroke-width="1" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#login-grid)" />
        </svg>

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-2">
            <a href="{{ route('landing') }}" class="flex items-center">
                {{-- Logo untuk mode terang --}}
                <img src="{{ asset('images/logo-light.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto hidden dark:block">

                {{-- Logo untuk mode gelap --}}
                <img src="{{ asset('images/logo-dark.svg') }}" alt="SIMS.Usaha" class="h-6 w-auto block dark:hidden">
            </a>
        </div>

        {{-- Teks Bawah --}}
        <div class="relative z-10 max-w-sm">
            <p class="text-sm text-white/70 font-medium leading-relaxed tracking-tight">
                Silakan masuk dengan akun terverifikasi Anda untuk memantau data dan mengelola seluruh laporan operasional.
            </p>
        </div>
    </div>

    {{-- Sisi Kanan: FORM LOGIN --}}
    <div class="w-full md:w-1/2 lg:w-[45%] flex flex-col p-6 sm:p-10 lg:p-12">

        {{-- Nav Atas --}}
        <div class="flex justify-start items-center mb-8">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors duration-150" aria-label="Kembali ke Beranda">
                <x-heroicon-o-arrow-left class="h-3.5 w-3.5" stroke-width="2" />
                Beranda
            </a>
        </div>

        {{-- Konten Tengah --}}
        <div class="flex-1 flex flex-col justify-center w-full max-w-md mx-auto lg:mx-0">

            {{-- Judul Login --}}
            <div class="mb-7">
                <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white tracking-tight">Login</h1>
                <p class="mt-1.5 text-xs text-neutral-400">Masuk dengan akun Anda untuk melanjutkan.</p>
            </div>

            {{-- Alert Error Session --}}
            @if (session()->has('error'))
                <div class="mb-6 p-3 text-xs text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 rounded-sm flex items-center gap-2" role="alert">
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Alert Status Session --}}
            @if (session()->has('status'))
                <div class="mb-6 p-3 text-xs text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/50 bg-emerald-50 dark:bg-emerald-950/30 rounded-sm" role="alert">
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validasi Error Global --}}
            @if ($errors->any() && !$errors->has('identity') && !$errors->has('password'))
                <div class="mb-6 p-3 text-xs text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 rounded-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form Utama --}}
            <form wire:submit="login" id="loginForm" class="space-y-6">
                <div class="grid grid-cols-1 gap-5">

                    {{-- Field Identity --}}
                    <div class="relative">
                        <label for="identity" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Username / NIP
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-user class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="identity"
                                id="identity"
                                type="text"
                                autocomplete="username"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-3 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="Masukkan ID"
                            >
                        </div>
                        @error('identity') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Field Password --}}
                    <div x-data="{ showPassword: false }" class="relative">
                        <label for="password" class="block text-xs font-medium text-neutral-600 dark:text-neutral-300 mb-1">
                            Password
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-lock-closed class="absolute left-3 h-4 w-4 text-neutral-400 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="password"
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="w-full bg-white dark:bg-slate-900 border border-neutral-200 dark:border-slate-700 rounded-sm py-2.5 pl-9 pr-9 text-sm text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-400 transition-colors"
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200 focus:outline-none"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!showPassword" class="h-4 w-4" stroke-width="1.8" />
                                <x-heroicon-o-eye-slash x-show="showPassword" x-cloak class="h-4 w-4" stroke-width="1.8" />
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p> @enderror
                    </div>

                </div>
            </form>

            {{-- Tombol Submit --}}
            <div class="mt-8 flex justify-end">
                <button
                    form="loginForm"
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-semibold text-white bg-blue-900 hover:bg-blue-950 rounded-sm transition-all shadow-sm shadow-blue-900/20 disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="login">Buka Dashboard</span>
                    <span wire:loading wire:target="login">Memproses...</span>

                    <x-heroicon-o-arrow-right wire:loading.remove wire:target="login" class="w-3.5 h-3.5" stroke-width="2.5" />
                    <svg wire:loading wire:target="login" class="animate-spin h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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