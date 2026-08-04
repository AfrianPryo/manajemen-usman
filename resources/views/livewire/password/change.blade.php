<div>
    <x-layouts.guest>
        <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h2 class="text-center text-3xl font-extrabold text-gray-900">
                        Ganti Password
                    </h2>
                    <p class="mt-2 text-center text-sm text-gray-600">
                        Anda harus mengganti password sebelum melanjutkan.
                    </p>
                </div>

                <form wire:submit.prevent="update" class="mt-8 space-y-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                            <input wire:model="current_password" type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            @error('current_password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                            <input wire:model="new_password" type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                            @error('new_password') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                            <input wire:model="new_password_confirmation" type="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2 px-4 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Simpan Password
                    </button>
                </form>
            </div>
        </div>
    </x-layouts.guest>
</div>