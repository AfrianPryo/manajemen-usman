<div class="min-h-screen w-full bg-slate-900 text-zinc-100 flex p-4 sm:p-6 lg:p-4 font-sans selection:bg-white selection:text-black overflow-hidden relative">

    {{-- Main Grid: sama seperti halaman login --}}
    <div class="w-full flex-1 grid grid-cols-1 md:grid-cols-12 gap-6 lg:gap-8 items-stretch">

        {{-- Sisi Kiri: Branding (tanpa objek 3D ASCII) --}}
        <div class="hidden md:flex md:col-span-6 lg:col-span-7 relative flex-col justify-between py-4 pr-4 select-none overflow-hidden rounded-[5px]">

            {{-- Footer Text Kiri Atas --}}
            <div class="relative z-10">
                <p class="text-md text-white font-bold tracking-tighter leading-none">Logo</p>
            </div>

            {{-- Footer Text Kiri Bawah --}}
            <div class="relative z-10">
                <p class="text-md text-white/80 font-medium tracking-tighter leading-none">
                    Perbarui password Anda secara berkala <br>
                    untuk menjaga keamanan akun dan <br>
                    seluruh data operasional Anda.
                </p>
            </div>
        </div>

        {{-- Sisi Kanan: KONTAINER FORM GANTI PASSWORD --}}
        <div class="md:col-span-6 lg:col-span-5 w-full flex flex-col justify-center">
            <div class="bg-blue-950 rounded-[5px] p-8 sm:p-10 lg:px-7 shadow-2xl relative flex flex-col justify-between w-full h-full min-h-[520px]">

                <div>
                    {{-- Header Atas Kontainer Form --}}
                    <div class="flex justify-between items-center mb-18">
                        @if (!auth()->user()?->must_change_password)
                            <a href="{{ route('dashboard') }}" class="text-white/80 hover:text-white transition duration-150 p-1.5 rounded-lg hover:bg-zinc-800/50" title="Kembali ke Dashboard" aria-label="Kembali ke Dashboard">
                                <x-heroicon-o-arrow-left class="h-5 w-5" stroke-width="1.8" />
                            </a>
                        @else
                            <span></span>
                        @endif
                    </div>

                    {{-- Judul --}}
                    <div class="mb-8">
                        <h1 class="text-3xl sm:text-4xl font-medium text-white tracking-tighter">Ganti Password</h1>
                        <p class="mt-3 text-xs text-white/50 tracking-tight">
                            @if (auth()->user()?->must_change_password)
                                Anda diwajibkan untuk mengganti password sebelum melanjutkan.
                            @else
                                Perbarui password Anda secara berkala untuk menjaga keamanan akun.
                            @endif
                        </p>
                    </div>

                    {{-- Alert Success --}}
                    @if (session()->has('message'))
                        <div class="mb-6 p-3 text-xs text-emerald-400 border-b border-emerald-500/50 bg-emerald-950/20" role="alert">
                            <span class="font-medium">{{ session('message') }}</span>
                        </div>
                    @endif

                    {{-- Alert Error --}}
                    @if (session()->has('error'))
                        <div class="mb-6 p-3 text-xs text-red-400 border-b border-red-500/50 bg-red-950/20" role="alert">
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Form Utama --}}
                    <form wire:submit="update" id="changePasswordForm" class="space-y-8">
                        <div class="grid grid-cols-1 gap-6">

                            {{-- Password Saat Ini --}}
                            <div x-data="{ show: false }" class="relative">
                                <label for="current_password" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                                    Password Saat Ini
                                </label>
                                <div class="relative">
                                    <input
                                        wire:model="current_password"
                                        id="current_password"
                                        :type="show ? 'text' : 'password'"
                                        autocomplete="current-password"
                                        class="w-full bg-transparent border-b border-white/70 py-2 pr-7 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white transition-colors rounded-none"
                                        placeholder="Masukkan password saat ini"
                                    >
                                    <button
                                        type="button"
                                        @click="show = !show"
                                        class="absolute right-0 bottom-2 text-zinc-500 hover:text-zinc-300 focus:outline-none"
                                        tabindex="-1"
                                    >
                                        <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
                                        <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                                    </button>
                                </div>
                                @error('current_password') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                            </div>

                            {{-- Password Baru --}}
                            <div x-data="{ show: false }" class="relative">
                                <label for="new_password" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                                    Password Baru
                                </label>
                                <div class="relative">
                                    <input
                                        wire:model="new_password"
                                        id="new_password"
                                        :type="show ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        class="w-full bg-transparent border-b border-white/70 py-2 pr-7 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white transition-colors rounded-none"
                                        placeholder="Minimal 8 karakter (huruf besar, kecil, angka)"
                                    >
                                    <button
                                        type="button"
                                        @click="show = !show"
                                        class="absolute right-0 bottom-2 text-zinc-500 hover:text-zinc-300 focus:outline-none"
                                        tabindex="-1"
                                    >
                                        <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />      
                                        <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                                    </button>
                                </div>
                                @error('new_password') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                            </div>

                            {{-- Konfirmasi Password Baru --}}
                            <div x-data="{ show: false }" class="relative">
                                <label for="new_password_confirmation" class="block text-[11px] font-medium text-white/60 uppercase tracking-tight mb-2">
                                    Konfirmasi Password Baru
                                </label>
                                <div class="relative">
                                    <input
                                        wire:model="new_password_confirmation"
                                        id="new_password_confirmation"
                                        :type="show ? 'text' : 'password'"
                                        autocomplete="new-password"
                                        class="w-full bg-transparent border-b border-white/70 py-2 pr-7 text-sm text-white placeholder-white/30 focus:outline-none focus:border-white transition-colors rounded-none"
                                        placeholder="Ulangi password baru"
                                    >
                                    <button
                                        type="button"
                                        @click="show = !show"
                                        class="absolute right-0 bottom-2 text-zinc-500 hover:text-zinc-300 focus:outline-none"
                                        tabindex="-1"
                                    >
                                        <x-heroicon-o-eye x-show="!show" class="h-4 w-4" stroke-width="1.8" />
                                        <x-heroicon-o-eye-slash x-show="show" x-cloak class="h-4 w-4" stroke-width="1.8" />
                                    </button>
                                </div>
                                @error('new_password_confirmation') <p class="mt-1 text-[11px] text-red-400 absolute left-0 -bottom-5">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </form>
                </div>

                {{-- Bottom Section dalam Kontainer Form --}}
                <div class="mt-12 pt-4 flex justify-end items-end">
                    <button
                        form="changePasswordForm"
                        type="submit"
                        wire:loading.attr="disabled"
                        class="group inline-flex items-center gap-2 rounded-[2px] bg-white pt-[4px] pb-[4px] pl-3 pr-1 text-[11px] font-medium text-blue-900 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none"
                    >
                        <span
                            class="login-text relative inline-flex items-center overflow-hidden text-[12px] font-[450] tracking-tight"
                            data-text="Simpan Password"
                        >
                            <span wire:loading.remove wire:target="update">Simpan Password</span>
                            <span wire:loading wire:target="update">Memproses...</span>
                        </span>

                        <span class="flex h-8 w-8 items-center justify-center rounded-[3px] bg-blue-900/90 shrink-0">
                            <x-heroicon-o-arrow-up-right 
                                wire:loading.remove 
                                wire:target="update" 
                                class="h-4 w-4 stroke-white transition-transform duration-300 group-hover:rotate-45" 
                                stroke-width="2.5" 
                            />

                            <svg
                                wire:loading
                                wire:target="update"
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