<?php

namespace App\Livewire\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = '';

    public function create(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'role']);
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()->name ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $this->editingId,
            'role' => 'required|exists:roles,name',
        ];
        $rules['password'] = $this->editingId ? 'nullable|min:6' : 'required|min:6';

        $data = $this->validate($rules);

        $user = User::updateOrCreate(['id' => $this->editingId], [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $this->password ? Hash::make($this->password) : (User::find($this->editingId)?->password ?? Hash::make(str()->random(10))),
        ]);

        $user->syncRoles([$data['role']]);

        $this->showModal = false;
        session()->flash('success', 'User berhasil disimpan.');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Kamu tidak bisa menghapus akun sendiri.');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('success', 'User berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.users.index', [
            'users' => User::with('roles')->latest()->paginate(10),
            'roles' => Role::pluck('name', 'name'),
        ]);
    }
}
