<?php

namespace App\Livewire\Master\Users;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Manajemen Admin')]
class Index extends Component
{
    use WithPagination;

    // Filter & Search
    public string $search = '';

    // State Modal & Form Input
    public bool $showCreateModal = false;

    public string $name = '';
    public string $employee_status = 'tetap'; // tetap, part_time, magang
    public string $nip = '';
    public ?int $unit_id = null;
    public string $role = 'unit-admin'; // unit-admin, master-admin

    // Store Kredensial Sementara setelah berhasil buat user
    public ?array $createdCredentials = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset(['name', 'employee_status', 'nip', 'unit_id', 'role']);
        $this->resetValidation();
    }

    public function save(): void
    {
        // 1. Validasi
        $rules = [
            'name'            => 'required|string|max:255',
            'employee_status' => 'required|in:tetap,part_time,magang',
            'role'            => 'required|in:master-admin,unit-admin',
            'unit_id'         => $this->role === 'unit-admin' ? 'required|exists:units,id' : 'nullable',
        ];

        if ($this->employee_status === 'tetap') {
            $rules['nip'] = 'required|numeric|digits:18|unique:users,nip';
        } else {
            $rules['nip'] = 'nullable|numeric|digits:18|unique:users,nip';
        }

        $this->validate($rules);

        // 2. Auto-generate Username & Password
        $username = $this->generateUniqueUsername();
        $plainPassword = 'Sims#' . rand(1000, 9999);

        // 3. Simpan Data User
        $user = User::create([
            'name'                 => $this->name,
            'employee_status'      => $this->employee_status,
            'nip'                  => $this->employee_status === 'tetap' ? $this->nip : null,
            'username'             => $username,
            'unit_id'              => $this->role === 'master-admin' ? null : $this->unit_id,
            'password'             => Hash::make($plainPassword),
            'must_change_password' => true,
            'is_active'            => true,
        ]);

        $user->assignRole($this->role);

        // 4. Kredensial untuk Alert Sukses
        $this->createdCredentials = [
            'name'     => $user->name,
            'username' => $username,
            'password' => $plainPassword,
            'status'   => ucfirst(str_replace('_', ' ', $user->employee_status)),
        ];

        $this->showCreateModal = false;
        $this->resetForm();
    }

    private function generateUniqueUsername(): string
    {
        // Pegawai tetap: Gunakan NIP murni angka
        if ($this->employee_status === 'tetap' && !empty($this->nip)) {
            return preg_replace('/[^0-9]/', '', $this->nip);
        }

        // Non-NIP: Slug nama + urutan jika terdapat duplikasi
        $baseSlug = Str::slug(Str::words($this->name, 2, ''), '.');
        $username = $baseSlug ?: 'user';
        $counter = 1;

        while (User::where('username', $username)->exists()) {
            $username = $baseSlug . $counter;
            $counter++;
        }

        return $username;
    }

    public function toggleUserStatus(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update(['is_active' => !$user->is_active]);
    }

    public function render()
    {
        $users = User::with(['unit', 'roles'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                        ->orWhere('username', 'like', "%{$this->search}%")
                        ->orWhere('nip', 'like', "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master.users.index', [
            'users' => $users,
            'units' => Unit::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}