<div class="w-full max-w-sm bg-white rounded-2xl shadow-xl p-8">
    <h1 class="text-xl font-bold text-gray-800 mb-1 text-center">Usaha Mandiri Sekolah</h1>
    <p class="text-sm text-gray-500 text-center mb-6">Masuk ke dashboard manajemen</p>

    <form wire:submit="login" class="space-y-4">
        <x-form-input label="Email" name="email" type="email" />
        <x-form-input label="Password" name="password" type="password" />

        <label class="flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" wire:model="remember" class="rounded border-gray-300">
            Ingat saya
        </label>

        <button type="submit" class="w-full bg-indigo-600 text-white text-sm font-medium py-2.5 rounded-lg hover:bg-indigo-700">
            Masuk
        </button>
    </form>
</div>
