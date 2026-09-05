<div class="min-h-screen w-full bg-slate-900 text-zinc-100 flex font-sans selection:bg-white selection:text-black overflow-hidden relative">

    {{-- Sisi Kiri: Branding --}}
    <div class="hidden md:flex md:w-1/2 lg:w-[58%] relative flex-col justify-between p-8 lg:p-12 select-none overflow-hidden">

        {{-- Logo --}}
        <div class="relative z-10 flex items-center gap-2">
            <span class="flex h-7 w-7 items-center justify-center rounded-[4px] border border-white/25 text-white">
                <x-heroicon-o-squares-2x2 class="h-4 w-4" stroke-width="1.8" />
            </span>
            <p class="text-md text-white font-bold tracking-tighter leading-none">Logo</p>
        </div>

        {{-- Dekorasi Garis Ringan (pengganti objek 3D ASCII, tanpa Three.js) --}}
        <svg class="absolute inset-0 z-0 h-full w-full pointer-events-none opacity-20" viewBox="0 0 800 800" fill="none" preserveAspectRatio="xMinYMax slice" aria-hidden="true">
            <path d="M-50 650 C 150 550, 250 750, 450 600 S 750 500, 900 620" stroke="#cbd5e1" stroke-width="1" />
            <path d="M-80 700 C 120 600, 220 800, 420 650 S 720 550, 880 680" stroke="#94a3b8" stroke-width="1" />
            <path d="M-60 600 C 140 520, 260 700, 470 560 S 760 470, 920 590" stroke="#64748b" stroke-width="1" />
            <path d="M-100 760 C 100 660, 240 840, 440 700 S 740 610, 900 730" stroke="#475569" stroke-width="1" />
        </svg>

        {{-- Teks Bawah --}}
        <div class="relative z-10">
            <p class="text-md text-white/80 font-medium tracking-tighter leading-none">Silakan masuk dengan <br>akun terverifikasi Anda untuk memantau data <br> dan mengelola seluruh laporan operasional.</p>
        </div>
    </div>

    {{-- Sisi Kanan: FORM LOGIN --}}
    <div class="w-full md:w-1/2 lg:w-[42%] bg-blue-950 flex flex-col p-6 sm:p-10 lg:p-12">

        {{-- Nav Atas --}}
        <div class="flex justify-start items-center mb-30">
            <a href="{{ route('landing') }}" class="inline-flex items-center gap-1.5 text-sm tracking-tighter text-white/70 hover:text-white transition duration-150" aria-label="Kembali ke Beranda">
                <x-heroicon-o-arrow-left class="h-4 w-4" stroke-width="2" />
                Beranda
            </a>
        </div>

        {{-- Konten Tengah --}}
        <div class="flex-1 flex flex-col justify-center w-full max-w-md mx-auto lg:mx-0">

            {{-- Judul Login --}}
            <div class="mb-8">
                <h1 class="text-3xl sm:text-4xl font-medium text-white tracking-tighter">Login</h1>
                <p class="mt-3 text-sm text-white/50 tracking-tight">Masuk dengan akun Anda untuk melanjutkan.</p>
            </div>

            {{-- Alert Error Session --}}
            @if (session()->has('error'))
                <div class="mb-6 p-3 text-xs text-red-400 border-b border-red-500/50 bg-red-950/20 flex items-center gap-2" role="alert">
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Alert Status Session --}}
            @if (session()->has('status'))
                <div class="mb-6 p-3 text-xs text-emerald-400 border-b border-emerald-500/50 bg-emerald-950/20" role="alert">
                    <span class="font-medium">{{ session('status') }}</span>
                </div>
            @endif

            {{-- Validasi Error Global --}}
            @if ($errors->any() && !$errors->has('identity') && !$errors->has('password'))
                <div class="mb-6 p-3 text-xs text-red-400 border-b border-red-500/50 bg-red-950/20">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Form Utama --}}
            <form wire:submit="login" id="loginForm" class="space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                    {{-- Field Identity --}}
                    <div class="relative">
                        <label for="identity" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                            Username / NIP
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-user class="absolute left-3 h-4 w-4 text-white/40 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="identity"
                                id="identity"
                                type="text"
                                autocomplete="username"
                                class="w-full bg-white/5 border border-white/10 rounded-[3px] py-2.5 pl-9 pr-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-colors"
                                placeholder="Masukkan ID"
                            >
                        </div>
                        @error('identity') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                    </div>

                    {{-- Field Password --}}
                    <div x-data="{ showPassword: false }" class="relative">
                        <label for="password" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                            Password
                        </label>
                        <div class="relative flex items-center">
                            <x-heroicon-o-lock-closed class="absolute left-3 h-4 w-4 text-white/40 pointer-events-none" stroke-width="1.8" />
                            <input
                                wire:model="password"
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="w-full bg-white/5 border border-white/10 rounded-[3px] py-2.5 pl-9 pr-9 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white/40 focus:bg-white/10 transition-colors"
                                placeholder="••••••••"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 text-white/40 hover:text-white/70 focus:outline-none"
                                tabindex="-1"
                            >
                                <x-heroicon-o-eye x-show="!showPassword" class="h-4 w-4" stroke-width="1.8" />
                                <x-heroicon-o-eye-slash x-show="showPassword" x-cloak class="h-4 w-4" stroke-width="1.8" />
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                    </div>

                </div>
            </form>

            {{-- Tombol Submit --}}
            <div class="mt-40 pt-4 flex justify-end items-end">
                <button
                    form="loginForm"
                    type="submit"
                    wire:loading.attr="disabled"
                    class="group inline-flex items-center gap-2 rounded-[2px] bg-white pt-[4px] pb-[4px] pl-3 pr-1 text-[11px] font-medium text-blue-900 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none"
                >
                    <span
                        class="login-text relative inline-flex items-center overflow-hidden text-[12px] font-[450] tracking-tight"
                        data-text="Login Admin"
                    >
                        Login Admin
                    </span>

                    <span class="flex h-8 w-8 items-center justify-center rounded-[3px] bg-blue-900/90 shrink-0">
                        <x-heroicon-o-arrow-up-right
                            wire:loading.remove
                            wire:target="login"
                            class="h-3 w-3 stroke-white transition-transform duration-300 group-hover:rotate-45"
                            stroke-width="2.5"
                        />
                        <svg
                            wire:loading
                            wire:target="login"
                            class="animate-spin h-3 w-3 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
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
        -webkit-text-fill-color: #ffffff !important;
        caret-color: #ffffff;
    }
</style>