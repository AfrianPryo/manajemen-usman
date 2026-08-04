<?php

namespace App\Livewire\Auth;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Login Admin')]
class Login extends Component
{
    public string $identity = '';
    public string $password = '';

    protected function rules(): array
    {
        return [
            'identity' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    protected function messages(): array
    {
        return [
            'identity.required' => 'Email atau NIP wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }

    public function login()
    {
        $this->validate();

        $throttleKey = Str::lower($this->identity) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            AuthLog::log('login.failed', null, $this->identity, 'Rate limit tercapai');

            $this->addError('identity', "Terlalu banyak percobaan login. Silakan coba lagi dalam {$minutes} menit.");
            return;
        }

        $user = User::where('email', $this->identity)
            ->orWhere('nip', $this->identity)
            ->first();

        if (!$user || !Hash::check($this->password, $user->password)) {
            RateLimiter::hit($throttleKey, 900);

            AuthLog::log('login.failed', $user?->id, $this->identity, 'Kredensial salah');

            $this->addError('identity', 'Email/NIP atau password salah.');
            return;
        }

        if (!$user->is_active) {
            AuthLog::log('login.failed', $user->id, $this->identity, 'Akun nonaktif');
            $this->addError('identity', 'Akun tidak aktif. Silakan hubungi Master Admin.');
            return;
        }

        if ($user->isUnitAdmin() && $user->unit && !$user->unit->is_active) {
            AuthLog::log('login.failed', $user->id, $this->identity, 'Unit nonaktif');
            $this->addError('identity', 'Unit sedang nonaktif. Silakan hubungi Master Admin.');
            return;
        }

        Auth::login($user);
        session()->regenerate();

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => session()->getId(),
        ]);

        RateLimiter::clear($throttleKey);
        AuthLog::log('login.success', $user->id, $this->identity, 'Login berhasil');

        if ($user->isMasterAdmin()) {
            return redirect()->route('master.dashboard');
        }

        if ($user->isUnitAdmin() && $user->unit) {
            return redirect()->route('unit.dashboard', $user->unit->slug);
        }

        return redirect('/');
    }

    // 🔴 HAPUS method render() karena sudah pakai Attribute #[Layout]
}