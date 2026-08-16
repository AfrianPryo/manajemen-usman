<?php

namespace App\Livewire\Master\Users;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $showCreateModal = false;

    // State Edit
    public bool $isEditing = false;
    public ?int $editingUserId = null;

    // Form Inputs
    public string $name = '';
    public string $employee_status = 'nip'; // Default: nip
    public string $nip = '';
    public string $role = 'unit-admin';
    public ?int $unit_id = null;

    // Alert / Modal Kredensial
    public ?array $createdCredentials = null;

    #[On('open-create-admin-modal')]
    public function openCreateModal(): void
    {
        $this->reset(['name', 'nip', 'unit_id', 'isEditing', 'editingUserId']);
        $this->employee_status = 'nip';
        $this->role = 'unit-admin';
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->reset(['name', 'nip', 'unit_id', 'isEditing', 'editingUserId']);
        $this->resetValidation();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:100',
            'employee_status' => 'required|in:nip,non_nip',
            'role' => 'required|in:master-admin,unit-admin',
        ];

        if ($this->employee_status === 'nip') {
            $rules['nip'] = 'required|numeric|digits:18|unique:users,nip';
        }

        if ($this->role === 'unit-admin') {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $this->validate($rules);

        // Auto-generate Username & Password
        $username = Str::slug($this->name, '.') . '.' . rand(100, 999);
        $plainPassword = Str::random(8);

        $user = User::create([
            'name' => $this->name,
            'username' => $username,
            'password' => Hash::make($plainPassword),
            'employee_status' => $this->employee_status,
            'nip' => $this->employee_status === 'nip' ? $this->nip : null,
            'unit_id' => $this->role === 'unit-admin' ? $this->unit_id : null,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($this->role);
        }

        $this->createdCredentials = [
            'title' => '🎉 Akun Admin Berhasil Dibuat!',
            'name' => $user->name,
            'username' => $user->username,
            'password' => $plainPassword,
        ];

        $this->closeCreateModal();
    }

    public function editUser(int $id): void
    {
        $user = User::findOrFail($id);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->employee_status = $user->employee_status ?? 'nip';
        $this->nip = $user->nip ?? '';

        if (method_exists($user, 'hasRole')) {
            $this->role = $user->hasRole('master-admin') ? 'master-admin' : 'unit-admin';
        } else {
            $this->role = method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin() ? 'master-admin' : 'unit-admin';
        }

        $this->unit_id = $user->unit_id;
        $this->isEditing = true;
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function updateUser(): void
    {
        if (!$this->editingUserId) {
            return;
        }

        $rules = [
            'name' => 'required|string|max:100',
            'employee_status' => 'required|in:nip,non_nip',
            'role' => 'required|in:master-admin,unit-admin',
        ];

        if ($this->employee_status === 'nip') {
            $rules['nip'] = 'required|numeric|digits:18|unique:users,nip,' . $this->editingUserId;
        }

        if ($this->role === 'unit-admin') {
            $rules['unit_id'] = 'required|exists:units,id';
        }

        $this->validate($rules);

        $user = User::findOrFail($this->editingUserId);

        $user->update([
            'name' => $this->name,
            'employee_status' => $this->employee_status,
            'nip' => $this->employee_status === 'nip' ? $this->nip : null,
            'unit_id' => $this->role === 'unit-admin' ? $this->unit_id : null,
        ]);

        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$this->role]);
        } elseif (method_exists($user, 'assignRole')) {
            $user->assignRole($this->role);
        }

        session()->flash('message', 'Data admin berhasil diperbarui.');
        $this->closeCreateModal();
    }

    public function toggleUserStatus($userId): void
    {
        if ((int) $userId === (int) auth()->id()) {
            return;
        }

        $user = User::findOrFail($userId);
        $user->is_active = !$user->is_active;
        $user->save();
    }

    public function resetPassword($userId): void
    {
        $user = User::findOrFail($userId);
        $newPassword = Str::random(8);

        $user->password = Hash::make($newPassword);
        $user->must_change_password = true;
        $user->save();

        $this->createdCredentials = [
            'title' => '🔑 Password Berhasil Direset!',
            'name' => $user->name,
            'username' => $user->username,
            'password' => $newPassword,
        ];
    }

    public function deleteUser(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        $user = User::withTrashed()->findOrFail($id);

        if ($user->is_active) {
            session()->flash('error', 'Akun admin hanya dapat dihapus jika statusnya NONAKTIF. Silakan nonaktifkan akun terlebih dahulu.');
            return;
        }

        if (method_exists($user, 'forceDelete')) {
            $user->forceDelete();
        } else {
            $user->delete();
        }

        session()->flash('message', 'Akun admin berhasil dihapus secara permanen.');
    }

    public function render()
    {
        $users = User::query()
            ->with(['unit', 'roles'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->orWhere('nip', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        $units = Unit::all();

        return view('livewire.master.users.index', [
            'users' => $users,
            'units' => $units,
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),
            'totalUnits' => Unit::count(),
            'activeUnits' => Unit::where('is_active', true)->count(),
            'inactiveUnits' => Unit::where('is_active', false)->count(),
        ]);
    }
}