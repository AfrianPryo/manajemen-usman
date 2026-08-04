<?php

namespace App\Livewire\Master\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Profil Saya')]
class Index extends Component
{
    public string $name = '';
    public string $email = '';
    public string $nip = '';
    public string $phone = '';
    public string $employee_status = '';

    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->nip = $user->nip ?? '';
        $this->phone = $user->phone ?? '';
        $this->employee_status = $user->employee_status ?? '';
    }

    public function updateProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'employee_status' => 'nullable|in:Guru,Pegawai,Siswa',
        ]);

        $user->update([
            'name' => $this->name,
            'nip' => $this->nip ?: null,
            'phone' => $this->phone ?: null,
            'employee_status' => $this->employee_status ?: null,
        ]);

        session()->flash('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $user = Auth::user();

        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ], [
            'new_password.regex' => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ]);

        $user->update([
            'password' => $this->new_password,
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('success', 'Password berhasil diubah.');
    }

    public function render()
    {
        return view('livewire.master.profile.index');
    }
}