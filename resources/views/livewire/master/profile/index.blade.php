<div class="p-6 max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil Saya</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi akun Anda.</p>
    </div>

    {{-- Flash Notifications (toast) --}}
    @if (session()->has('success_profile'))
        <div wire:key="toast-success-profile-{{ md5(session('success_profile')) }}" x-data x-init="$store.toast.push('success', @js(session('success_profile')))"></div>
    @endif

    @if (session()->has('success_password'))
        <div wire:key="toast-success-password-{{ md5(session('success_password')) }}" x-data x-init="$store.toast.push('success', @js(session('success_password')))"></div>
    @endif

    {{-- Edit Profile --}}
    <form novalidate wire:submit="updateProfile" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700 pb-3">Informasi Profil</h2>

        {{-- Foto Profil --}}
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xl overflow-hidden shrink-0">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview foto profil" class="w-full h-full object-cover">
                @elseif ($existingAvatar)
                    <img src="{{ asset('storage/' . $existingAvatar) }}" alt="Foto profil" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($name ?: 'U', 0, 1)) }}
                @endif
            </div>
            <div
                class="flex-1"
                x-data="{ uploading: false, progress: 0 }"
                x-on:livewire-upload-start="uploading = true; progress = 0"
                x-on:livewire-upload-finish="uploading = false"
                x-on:livewire-upload-cancel="uploading = false"
                x-on:livewire-upload-error="uploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Profil</label>
                <input type="file" wire:model="avatar" accept="image/*" class="@error('avatar') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" aria-invalid="@error('avatar') true @else false @enderror">
                <x-upload-progress />
                @error('avatar') <x-form-error :message="$message" :field="'avatar'" /> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" class="@error('name') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('name') true @else false @enderror">
                @error('name') <x-form-error :message="$message" :field="'name'" /> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" wire:model="email" class="@error('email') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('email') true @else false @enderror">
                @error('email') <x-form-error :message="$message" :field="'email'" /> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP</label>
                <input type="text" wire:model="phone" inputmode="numeric" oninput="onlyDigits(event)" class="@error('phone') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('phone') true @else false @enderror">
                @error('phone') <x-form-error :message="$message" :field="'phone'" /> @enderror
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
            <button type="submit" wire:loading.attr="disabled" wire:target="avatar,updateProfile" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">Update Profil</button>
        </div>
    </form>

    {{-- Change Password --}}
    {{-- Section ini HANYA muncul kalau canChangePassword() true. Untuk
         Admin Unit (App\Livewire\Unit\Profile\Index) method ini di-override
         jadi false, karena password Admin Unit sengaja hanya boleh diubah
         oleh Master Admin lewat Master > Admin, bukan mandiri dari sini. --}}
    @if ($this->canChangePassword())
        <form wire:submit="updatePassword" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-4">
            <h2 class="font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700 pb-3">Ganti Password</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Lama <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="current_password" class="@error('current_password') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('current_password') true @else false @enderror">
                    @error('current_password') <x-form-error :message="$message" :field="'current_password'" /> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="new_password" class="@error('new_password') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('new_password') true @else false @enderror">
                    @error('new_password') <x-form-error :message="$message" :field="'new_password'" /> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi <span class="text-red-500">*</span></label>
                    <input type="password" wire:model="new_password_confirmation" class="@error('new_password_confirmation') !border-rose-400 !focus:border-rose-500 !focus:ring-rose-500/10 @enderror w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm" aria-invalid="@error('new_password_confirmation') true @else false @enderror">
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
                <button type="submit" class="px-6 py-2 bg-blue-900 text-white rounded-lg text-sm font-semibold hover:bg-blue-950">Ganti Password</button>
            </div>
        </form>
    @else
        <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-gray-200 dark:border-slate-700 p-6 text-sm text-gray-500 dark:text-gray-400">
            Perubahan password Admin Unit hanya dapat dilakukan oleh Master Admin melalui menu <span class="font-medium text-gray-700 dark:text-gray-300">Master &raquo; Admin</span>.
        </div>
    @endif
</div>