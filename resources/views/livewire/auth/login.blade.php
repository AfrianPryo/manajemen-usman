<div class="min-h-screen w-full bg-slate-900 text-zinc-100 flex p-4 sm:p-6 lg:p-4 font-sans selection:bg-white selection:text-black overflow-hidden relative">

    {{-- Main Grid: Mengisi 100% area tanpa max-w-7xl agar jarak kanan simetris dengan atas & bawah --}}
    <div class="w-full flex-1 grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-stretch">

        {{-- Sisi Kiri: Branding & 3D ASCII Hero --}}
        <div class="hidden md:flex md:col-span-6 lg:col-span-7 relative flex-col justify-between py-4 pr-4 select-none overflow-hidden rounded-[5px]">

            {{-- Footer Text Kiri --}}
            <div class="relative z-10">
                <p class="text-md text-white font-bold tracking-tighter leading-none">Logo</p>
            </div>

            {{-- 🟢 CONTAINER OBJEK 3D ASCII --}}
            <div id="ascii-hero-container" 
                wire:ignore 
                class="absolute inset-0 z-0 flex items-center justify-center overflow-hidden pointer-events-none text-slate-200 brightness-50 contrast-125 [&_table]:text-slate-200 [&_pre]:text-slate-200">
            </div>
            {{-- Footer Text Kiri --}}
            <div class="relative z-10">
                <p class="text-md text-white/80 font-medium tracking-tighter leading-none">Silakan masuk dengan <br >akun terverifikasi Anda untuk memantau data <br> dan mengelola seluruh laporan operasional.</p>
            </div>
        </div>

        {{-- Sisi Kanan: KONTAINER FORM LOGIN --}}
        <div class="md:col-span-6 lg:col-span-5 w-full flex flex-col justify-center">
            <div class="bg-blue-950 rounded-[5px] p-8 sm:p-10 lg:px-7 shadow-2xl relative flex flex-col justify-between w-full h-full min-h-[520px]">

                <div>
                    {{-- Header Atas Kontainer Form --}}
                    <div class="flex justify-between items-center mb-18">
                        <a href="{{ route('landing') }}" class="text-white/80 hover:text-white transition duration-150 ml-auto p-1.5 rounded-lg hover:bg-zinc-800/50" title="Kembali ke Beranda" aria-label="Kembali ke Beranda">
                            <x-heroicon-o-home class="h-5 w-5" stroke-width="1.8" />
                        </a>
                    </div>

                    {{-- Judul Login --}}
                    <div class="mb-12">
                        <h1 class="text-3xl sm:text-4xl font-medium text-white tracking-tighter">Login</h1>
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
                                <input
                                    wire:model="identity"
                                    id="identity"
                                    type="text"
                                    autocomplete="username"
                                    class="w-full bg-transparent border-b border-white/70 py-2 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white transition-colors rounded-none"
                                    placeholder="Masukkan ID"
                                >
                                @error('identity') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                            </div>

                            {{-- Field Password --}}
                            <div x-data="{ showPassword: false }" class="relative">
                                <label for="password" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        wire:model="password"
                                        id="password"
                                        :type="showPassword ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="w-full bg-transparent border-b border-white/70 py-2 pr-7 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white transition-colors rounded-none"
                                        placeholder="••••••••"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showPassword = !showPassword" 
                                        class="absolute right-0 bottom-2 text-zinc-500 hover:text-zinc-300 focus:outline-none"
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
                </div>

                {{-- Bottom Section dalam Kontainer Form --}}
                <div class="mt-12 pt-4 flex justify-end items-end">
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

<script>
    let asciiHeroInstance = null;

    function renderAscii() {
        // Cek apakah canvas sudah ada di dalam container, jika ada tidak perlu render ulang
        const container = document.querySelector('#ascii-hero-container');
        if (!container) return;

        if (asciiHeroInstance && container.children.length > 0) {
            return; // Elemen 3D masih ada & aman
        }

        if (asciiHeroInstance) {
            asciiHeroInstance.destroy();
        }

        if (typeof window.initAsciiHero === 'function') {
            asciiHeroInstance = window.initAsciiHero({
                containerSelector: '#ascii-hero-container',
                modelUrl: @json(asset('models/hero.glb')),
                characters: ' .:-=+*#%@', 
                resolution: 0.22,
                modelScale: 1,
                autoRotate: false,
                rotateSpeed: 0.3,
                tiltCursor: true,
                cursorSource: 'window',
            });
        }
    }

    document.addEventListener('DOMContentLoaded', renderAscii);
    document.addEventListener('livewire:navigated', renderAscii);

    // Cadangan: Re-check jika Livewire melakukan update komponen
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('commit', ({ respond }) => {
            respond(() => {
                setTimeout(renderAscii, 50);
            });
        });
    });
</script>