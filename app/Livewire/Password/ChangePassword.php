<?php

namespace App\Livewire\Password;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Ganti Password')]
class ChangePassword extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'new_password'     => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini salah.',
            'new_password.required'             => 'Password baru wajib diisi.',
            'new_password.min'                  => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak sama.',
            'new_password.regex'                => 'Password harus mengandung huruf besar, huruf kecil, dan angka.',
        ];
    }

    public function update()
    {
        $this->validate();

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Update password ter-hash dan matikan flag must_change_password
        $user->update([
            'password'             => Hash::make($this->new_password),
            'must_change_password' => false,
        ]);

        AuthLog::log('password.changed', $user->id, $user->email, 'Password berhasil diubah');

        session()->flash('message', 'Password berhasil diubah.');

        // Role-Based Routing setelah berhasil ganti password
        if ($user->isMasterAdmin()) {
            return redirect()->route('master.dashboard');
        }

        if ($user->isUnitAdmin() && $user->unit) {
            return redirect()->route('unit.dashboard', $user->unit->slug);
        }

        return redirect()->route('landing');
    }

    public function render()
    {
        return view('livewire.password.change-password');
    }
}