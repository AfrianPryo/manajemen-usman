<div class="p-6 max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil Saya</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Kelola informasi akun Anda.</p>
    </div>

    @if (session()->has('success'))
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Edit Profile --}}
    <form wire:submit="updateProfile" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700 pb-3">Informasi Profil</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                <input type="text" wire:model="name" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email (readonly)</label>
                <input type="email" value="{{ $email }}" disabled class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-gray-50 dark:bg-slate-900/50 text-sm text-gray-500 cursor-not-allowed">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">NIP</label>
                <input type="text" wire:model="nip" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. HP</label>
                <input type="text" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status Pegawai</label>
                <select wire:model="employee_status" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
                    <option value="">-- Pilih --</option>
                    <option value="Guru">Guru</option>
                    <option value="Pegawai">Pegawai</option>
                    <option value="Siswa">Siswa</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700">Update Profil</button>
        </div>
    </form>

    {{-- Change Password --}}
    <form wire:submit="updatePassword" class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 p-6 space-y-4">
        <h2 class="font-semibold text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700 pb-3">Ganti Password</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Lama</label>
                <input type="password" wire:model="current_password" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
                @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                <input type="password" wire:model="new_password" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
                @error('new_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi</label>
                <input type="password" wire:model="new_password_confirmation" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-900 text-sm">
            </div>
        </div>
        <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-slate-700">
            <button type="submit" class="px-6 py-2 bg-amber-600 text-white rounded-lg text-sm font-semibold hover:bg-amber-700">Ganti Password</button>
        </div>
    </form>
</div>