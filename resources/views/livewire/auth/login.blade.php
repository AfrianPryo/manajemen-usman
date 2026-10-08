<x-auth-card title="Selamat datang!">
    <x-slot:subtitle>Masuk dengan akun Anda untuk melanjutkan.</x-slot:subtitle>

    {{-- Alert Error & Status Session (toast) --}}
    @if (session()->has('error'))
        <div wire:key="toast-error-{{ md5(session('error')) }}" x-data x-init="$store.toast.push('error', @js(session('error')))"></div>
    @endif
    @if (session()->has('status'))
        <div wire:key="toast-status-{{ md5(session('status')) }}" x-data x-init="$store.toast.push('success', @js(session('status')))"></div>
    @endif

    {{-- Validasi Error Global --}}
    @if ($errors->any() && !$errors->has('identity') && !$errors->has('password'))
        <div class="mb-5 p-3 text-xs text-rose-600 dark:text-rose-400 border border-rose-100 dark:border-rose-800/50 bg-rose-50 dark:bg-rose-950/30 rounded-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form Utama --}}
    <form wire:submit="login" id="loginForm" class="space-y-5">

        {{-- Field Identity --}}
        <div>
            <label for="identity" class="mb-1 block text-xs font-medium text-neutral-600 dark:text-neutral-300">
                Username / NIP
                <span class="text-red-500">*</span>
            </label>
            <input
                wire:model="identity"
                id="identity"
                type="text"
                autocomplete="username"
                class="@error('identity') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full rounded-sm border border-neutral-200 bg-white px-3 py-2.5 text-sm text-neutral-900 placeholder-neutral-400 transition-colors focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500/10 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                placeholder="Masukkan ID"
                aria-invalid="@error('identity') true @else false @enderror"
                aria-required="true"
            >
            @error('identity') <x-form-error :message="$message" :field="'identity'" /> @enderror
        </div>

        {{-- Field Password --}}
        <x-auth-password
            wire:model="password"
            id="password"
            label="Password"
            autocomplete="current-password"
            placeholder="••••••••"
            aria-required="true"
        />
    </form>

    {{-- Tombol Submit --}}
    <div class="mt-6">
        <x-auth-submit form="loginForm" target="login" label="Buka Dashboard" />
    </div>

    <x-slot:below>
        <a href="{{ route('landing') }}" class="mt-5 inline-flex items-center gap-1.5 text-xs font-semibold text-neutral-400 transition-colors duration-150 hover:text-neutral-700 dark:hover:text-neutral-200" aria-label="Kembali ke Beranda">
            <x-heroicon-o-arrow-left class="h-3.5 w-3.5" stroke-width="2" />
            Beranda
        </a>
    </x-slot:below>
</x-auth-card>
