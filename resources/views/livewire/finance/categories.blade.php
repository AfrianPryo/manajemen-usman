<div>
    <div class="flex items-center justify-end mb-4">
        <button wire:click="create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Tambah Kategori
        </button>
    </div>

    <x-data-table :headers="['Nama Kategori', 'Tipe', 'Aksi']">
        @forelse($categories as $category)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $category->name }}</td>
                <td class="px-4 py-3">
                    @if($category->type === 'income')
                        <x-badge color="green">Pemasukan</x-badge>
                    @else
                        <x-badge color="red">Pengeluaran</x-badge>
                    @endif
                </td>
                <td class="px-4 py-3 space-x-2">
                    <button wire:click="edit({{ $category->id }})" class="text-indigo-600 hover:underline text-sm">Edit</button>
                    <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus kategori ini?" class="text-blue-900 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit Kategori Keuangan' : 'Tambah Kategori Keuangan' }}">
        <x-form-input label="Nama Kategori" name="name" />
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe</label>
            <select wire:model="type" class="w-full rounded-lg border-gray-300 text-sm">
                <option value="income">Pemasukan</option>
                <option value="expense">Pengeluaran</option>
            </select>
        </div>
        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
