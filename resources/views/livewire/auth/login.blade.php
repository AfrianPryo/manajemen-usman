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
                
                {{-- Error Global --}}
                @if ($errors->any() && !$errors->has('identity') && !$errors->has('password'))
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-md text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="space-y-4">
                    {{-- Field Email/NIP --}}
                    <div>
                        <label for="identity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email atau NIP
                        </label>
                        <input
                            wire:model="identity"
                            id="identity"
                            type="text"
                            autocomplete="username"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white"
                            placeholder="Masukkan email atau NIP"
                        >
                        @error('identity') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    {{-- Field Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Password
                        </label>
                        <input
                            wire:model="password"
                            id="password"
                            type="password"
                            autocomplete="current-password"
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white"
                            placeholder="Masukkan password"
                        >
                        @error('password') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Submit Button --}}
                <div>
                    <button
                        type="submit"
                        class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-300"
                    >
                        Login
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