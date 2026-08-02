<div>
    <div class="flex items-center justify-end mb-4">
        <button wire:click="create" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg hover:bg-indigo-700">
            + Tambah User
        </button>
    </div>

    <x-data-table :headers="['Nama', 'Email', 'Role', 'Aksi']">
        @forelse($users as $user)
            <tr class="border-b hover:bg-gray-50">
                <td class="px-4 py-3 font-medium text-gray-800">{{ $user->name }}</td>
                <td class="px-4 py-3 text-gray-500">{{ $user->email }}</td>
                <td class="px-4 py-3"><x-badge color="indigo">{{ $user->roles->first()->name ?? '-' }}</x-badge></td>
                <td class="px-4 py-3 space-x-2">
                    <button wire:click="edit({{ $user->id }})" class="text-indigo-600 hover:underline text-sm">Edit</button>
                    <button wire:click="delete({{ $user->id }})" wire:confirm="Yakin ingin menghapus user ini?" class="text-red-600 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        @empty
        @endforelse
    </x-data-table>

    <div class="mt-4">{{ $users->links() }}</div>

    <x-modal wire:model="showModal" title="{{ $editingId ? 'Edit User' : 'Tambah User' }}">
        <x-form-input label="Nama" name="name" />
        <x-form-input label="Email" name="email" type="email" />
        <x-form-input label="Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}" name="password" type="password" />
        <x-form-select label="Role" name="role" :options="$roles" />
        <x-slot:footer>
            <button @click="show = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
            <button wire:click="save" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Simpan</button>
        </x-slot:footer>
    </x-modal>
</div>
