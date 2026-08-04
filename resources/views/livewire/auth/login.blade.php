<div>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            
            {{-- Header --}}
            <div class="text-center">
                <h1 class="font-display text-3xl font-bold text-blue-900 dark:text-blue-400 tracking-tighter">
                    SIMS<span class="text-slate-400 dark:text-slate-500 font-medium">.Usaha</span>
                </h1>
                <h2 class="mt-6 text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                    Login Admin
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Silakan login untuk mengakses dashboard
                </p>
            </div>

            {{-- Form Login --}}
            <form wire:submit="login" class="mt-8 space-y-6">

                {{-- 🟢 Alert Flash Session Error (Termasuk Sesi Berakhir dari Single Session Middleware) --}}
                @if (session()->has('error'))
                    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800" role="alert">
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 inline w-4 h-4 me-2 fill-current" viewBox="0 0 20 20">
                                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 15a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-4a1 1 0 0 1-2 0V6a1 1 0 0 1 2 0v5Z"/>
                            </svg>
                            <span class="font-medium">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                {{-- Flash Session Status (Pengoperasian Sukses/Notifikasi Umum) --}}
                @if (session()->has('status'))
                    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800" role="alert">
                        <span class="font-medium">{{ session('status') }}</span>
                    </div>
                @endif

                {{-- Error Global Validasi --}}
                @if ($errors->any() && !$errors->has('identity') && !$errors->has('password'))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-4">
                    {{-- Field Identity (Username / Email / NIP) --}}
                    <div>
                        <label for="identity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Username / Email / NIP
                        </label>
                        <input
                            wire:model="identity"
                            id="identity"
                            type="text"
                            autocomplete="username"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm"
                            placeholder="Masukkan username, email, atau NIP"
                        >
                        @error('identity') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Field Password dengan Toggle Lock/Hide (Alpine.js) --}}
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <div class="relative mt-1">
                            <input
                                wire:model="password"
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                class="block w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm"
                                placeholder="Masukkan password"
                            >
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
                                tabindex="-1"
                            >
                                <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.52 10.52 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Submit Button dengan Loading State --}}
                <div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-300"
                    >
                        <span wire:loading.remove wire:target="login">Login</span>
                        <span wire:loading wire:target="login" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>

            {{-- Link Kembali --}}
            <div class="text-center">
                <a href="{{ route('landing') }}" class="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>