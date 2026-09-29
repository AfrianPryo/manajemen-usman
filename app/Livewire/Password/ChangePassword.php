<?php

namespace App\Livewire\Password;

use App\Models\AuthLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
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

    // ================= SETUP NOMOR WA AKTIF (LOGIN PERTAMA) =================
    // Step tambahan KHUSUS untuk akun yang belum punya nomor WA terdaftar
    // ('phone' kosong) -- pada praktiknya ini hanya akun Master Admin awal
    // hasil MasterAdminSeeder (kredensial "dari dev"), karena akun lain yang
    // dibuat lewat Master\Users\Index atau Master\Dashboard (buat Admin Unit)
    // sudah MEWAJIBKAN nomor HP/WA diisi saat akun dibuat.
    //
    // Kalau $needsPhoneSetup === false (nomor sudah ada), alur TETAP satu
    // langkah seperti sebelumnya -- tidak ada perubahan perilaku untuk Admin
    // Unit ataupun akun yang di-reset kredensialnya (lihat NotificationSidebar
    // & Master\Notifications\Index yang men-set must_change_password=true
    // lagi, tapi 'phone' milik user itu tidak pernah dikosongkan).
    public int $step = 1;
    public string $phone = '';
    public bool $needsPhoneSetup = false;

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $this->needsPhoneSetup = (bool) ($user && empty($user->phone));
    }

    /**
     * True selama akun ini masih WAJIB ganti password (kredensial awal dari
     * dev / Master Admin). SENGAJA dibaca langsung dari user yang login pada
     * setiap request, BUKAN disimpan di public property Livewire, karena
     * public property bisa dimanipulasi dari sisi client.
     */
    protected function isForcedChange(): bool
    {
        return (bool) Auth::user()?->must_change_password;
    }

    protected function passwordRules(): array
    {
        $rules = [
            'new_password' => [
                'required',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
                // Tanpa kolom "password lama" (mode wajib ganti), pastikan
                // password baru tidak sama dengan password sementara tadi.
                function (string $attribute, mixed $value, \Closure $fail) {
                    $user = Auth::user();

                    if ($user && Hash::check((string) $value, $user->password)) {
                        $fail('Password baru tidak boleh sama dengan password saat ini.');
                    }
                },
            ],
        ];

        // Password lama hanya diminta untuk ganti password SUKARELA. Saat
        // wajib ganti (login pertama / hasil reset), user baru saja
        // membuktikan password itu lewat form login, jadi tidak diminta lagi.
        if (! $this->isForcedChange()) {
            $rules = ['current_password' => ['required', 'current_password']] + $rules;
        }

        return $rules;
    }

    protected function phoneRules(): array
    {
        /** @var User|null $user */
        $user = Auth::user();

        return [
            // Format & aturan unique disamakan dengan validasi nomor WA di
            // Master\Users\Index dan Master\Dashboard supaya konsisten.
            'phone' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+]+$/',
                Rule::unique('users', 'phone')->ignore($user?->id),
            ],
        ];
    }

    protected function rules(): array
    {
        return array_merge(
            $this->passwordRules(),
            $this->needsPhoneSetup ? $this->phoneRules() : []
        );
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
            'phone.required'                    => 'Nomor WhatsApp aktif wajib diisi.',
            'phone.regex'                        => 'Format nomor tidak valid. Hanya boleh angka (dan awalan +).',
            'phone.unique'                       => 'Nomor WhatsApp ini sudah terdaftar pada akun lain.',
        ];
    }

    /**
     * Validasi langkah 1 (ganti password) lalu pindah tampilan ke langkah 2
     * (setup nomor WA) TANPA menyimpan apa pun ke database dulu. Penyimpanan
     * sebenarnya (password + nomor WA sekaligus) baru terjadi di update() pada
     * langkah terakhir, supaya tidak ada state "setengah tersimpan" kalau user
     * me-refresh halaman di tengah proses (paling buruk cuma perlu mengulang
     * isi password).
     */
    public function nextStep(): void
    {
        if (!$this->needsPhoneSetup) {
            // Jaga-jaga kalau method ini terpanggil padahal harusnya cuma 1
            // langkah -- langsung proses seperti alur lama.
            $this->update();
            return;
        }

        $this->validate($this->passwordRules(), $this->messages());

        $this->step = 2;
    }

    public function backStep(): void
    {
        $this->step = 1;
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
        $payload = [
            'password'             => Hash::make($this->new_password),
            'must_change_password' => false,
        ];

        if ($this->needsPhoneSetup) {
            $payload['phone'] = $this->phone;
        }

        $user->update($payload);

        AuthLog::log(
            'password.changed',
            $user->id,
            $user->email,
            $this->needsPhoneSetup
                ? 'Password berhasil diubah & nomor WhatsApp aktif berhasil didaftarkan (login pertama)'
                : 'Password berhasil diubah'
        );

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