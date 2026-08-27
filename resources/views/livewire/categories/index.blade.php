<div>
    <div class="flex items-center justify-between mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari kategori..."
               class="w-64 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        <button wire:click="create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Tambah Kategori
        </button>
    </div>

    <x-data-table :headers="['Nama Kategori', 'Deskripsi', 'Jumlah Produk', 'Aksi']">
        @forelse($categories as $category)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $category->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $category->description ?: '-' }}</td>
                <td class="px-4 py-3"><x-badge color="gray">{{ $category->products_count }} produk</x-badge></td>
                <td class="px-4 py-3 space-x-2">
                    <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:underline text-sm">Edit</button>
                    <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori ini?" class="text-blue-900 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $categories->links() }}</div>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Kategori' : 'Tambah Kategori' }}">
        <x-form-input label="Nama Kategori" name="name" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea wire:model="description" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
        </div>
        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
