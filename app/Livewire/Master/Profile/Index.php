<?php

namespace App\Livewire\Master\Profile; // <-- Pastikan ini Profile, BUKAN Settings

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public $avatar;
    public ?string $existingAvatar = null;

    // Password Form
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = $user->name ?? '';
        $this->email = $user->email ?? '';
        $this->phone = $user->phone ?? '';
        $this->existingAvatar = $user->avatar ?? null;
    }

    public function updateProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name'   => 'required|string|max:100',
            'email'  => 'required|email|max:100|unique:users,email,' . $user->id,
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $data = [
            'name'  => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
        ];

        if ($this->avatar) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $this->avatar->store('avatars', 'public');
            $this->existingAvatar = $data['avatar'];
            $this->reset('avatar');
        }

        $user->update($data);
        session()->flash('success_profile', 'Profil pengguna berhasil diperbarui.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|current_password',
            'new_password'     => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        session()->flash('success_password', 'Kata sandi berhasil diubah.');
    }

    public function render()
    {
        return view('livewire.master.profile.index');
    }
}