<?php

namespace App\Livewire\Password;

use App\Models\AuthLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\FonnteOtpService;
use App\Support\PhoneNumber;
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

    // ================= VERIFIKASI KEPEMILIKAN NOMOR WA (OTP) =================
    // Di langkah 2, nomor WA harus dibuktikan aktif & dipegang orangnya lewat
    // kode OTP WhatsApp (memakai ulang FonnteOtpService, purpose 'phone_setup').
    //
    // $otpSent & $otp HANYA state tampilan. Bukti verifikasi yang sebenarnya
    // disimpan di SESSION server (bukan public property, yang bisa dimanipulasi
    // dari client) dan dicocokkan ulang dengan nomor final di update().
    public bool $otpSent = false;
    public string $otp = '';

    protected const OTP_PURPOSE = 'phone_setup';
    protected const SESSION_VERIFIED = 'change_password.phone_verified';
    protected const SESSION_SEND_FAILED = 'change_password.otp_send_failed';
    protected const SESSION_SKIPPED = 'change_password.phone_verification_skipped';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        $this->needsPhoneSetup = (bool) ($user && empty($user->phone));

        // Mulai bersih: bukti verifikasi dari sesi/percobaan lama tidak boleh terbawa.
        $this->resetPhoneVerification();
    }

    /**
     * Nomor diubah setelah OTP dikirim/diverifikasi -> bukti lama tidak berlaku
     * lagi. Mencegah "verifikasi nomor A lalu simpan nomor B".
     */
    public function updatedPhone(): void
    {
        $this->resetPhoneVerification();
    }

    protected function resetPhoneVerification(): void
    {
        if ($userId = Auth::id()) {
            app(FonnteOtpService::class)->invalidate($userId, self::OTP_PURPOSE);
        }

        session()->forget([self::SESSION_VERIFIED, self::SESSION_SEND_FAILED, self::SESSION_SKIPPED]);

        $this->otpSent = false;
        $this->otp = '';
    }

    /**
     * Verifikasi boleh dilewati HANYA kalau memang tidak mungkin dilakukan
     * (token Fonnte belum diisi, atau pengiriman OTP gagal karena provider),
     * supaya Master Admin tidak terkunci di login pertama. Dicek di server.
     */
    protected function canSkipVerification(): bool
    {
        return empty(Setting::get('wa_api_key')) || (bool) session(self::SESSION_SEND_FAILED);
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
            // Nomor disimpan dalam format E.164 ("+6281234567890"), dikirim
            // oleh komponen <x-phone-input>. Nilai mentah dinormalisasi dulu
            // di normalizePhone() sebelum divalidasi.
            'phone' => [
                'required',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! PhoneNumber::isValid((string) $value)) {
                        $fail('Nomor WhatsApp tidak valid. Nomor Indonesia harus diawali 8 (contoh: 812-3456-7890).');
                    }
                },
                // Unique dicek terhadap SEMUA bentuk penulisan (+62812.., 62812..,
                // 0812..) supaya data lama berformat "08xxx" tetap terdeteksi.
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $exists = User::withTrashed()
                        ->whereIn('phone', PhoneNumber::variants((string) $value))
                        ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
                        ->exists();

                    if ($exists) {
                        $fail('Nomor WhatsApp ini sudah terdaftar pada akun lain.');
                    }
                },
            ],
        ];
    }

    /**
     * Rapikan input nomor ke format E.164 sebelum validasi/simpan. Browser
     * sudah mengirim format ini lewat <x-phone-input>, tetapi server tidak
     * boleh bergantung pada JavaScript di sisi client.
     */
    protected function normalizePhone(): void
    {
        if ($this->needsPhoneSetup) {
            $this->phone = PhoneNumber::normalize($this->phone);
        }
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
            'phone.max'                         => 'Nomor WhatsApp terlalu panjang.',
            'otp.required'                      => 'Kode OTP wajib diisi.',
            'otp.digits'                        => 'Kode OTP harus 6 digit angka.',
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

    /**
     * Kirim (atau kirim ulang) kode OTP ke nomor WA yang diinput. Nomor
     * divalidasi format & keunikannya dulu sebelum kuota Fonnte dipakai.
     */
    public function sendPhoneOtp(): void
    {
        if (! $this->needsPhoneSetup || $this->step !== 2) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            return;
        }

        $this->normalizePhone();
        $this->validate($this->phoneRules(), $this->messages());

        // Token belum diisi -> OTP mustahil terkirim. Jangan biarkan admin buntu.
        if (empty(Setting::get('wa_api_key'))) {
            session()->put(self::SESSION_SEND_FAILED, true);
            $this->addError('phone', 'Pengiriman WhatsApp belum dikonfigurasi (token Fonnte kosong), jadi nomor belum bisa diverifikasi sekarang. Anda dapat melewati verifikasi dan memverifikasi nanti.');
            return;
        }

        $result = app(FonnteOtpService::class)->generateAndSend($user->id, self::OTP_PURPOSE, $this->phone);

        if (! $result['success']) {
            if (($result['reason'] ?? null) === 'send_failed') {
                session()->put(self::SESSION_SEND_FAILED, true);
            }

            // Sudah pernah masuk tahap OTP (kirim ulang) -> tampilkan di kolom OTP.
            $this->addError($this->otpSent ? 'otp' : 'phone', $result['message']);
            return;
        }

        session()->forget(self::SESSION_SEND_FAILED);
        $this->otp = '';
        $this->otpSent = true;
        $this->resetErrorBag();

        session()->flash('message', $result['message']);
    }

    /**
     * Cek kode OTP. Kalau valid, catat di SESSION bahwa nomor INI terverifikasi,
     * lalu lanjut menyimpan password + nomor lewat update() (yang memeriksa ulang bukti itu).
     */
    public function verifyOtp()
    {
        if (! $this->needsPhoneSetup || $this->step !== 2 || ! $this->otpSent) {
            return;
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $this->validate([
            'otp' => ['required', 'digits:6'],
        ], $this->messages());

        $this->normalizePhone();

        $result = app(FonnteOtpService::class)->verify($user->id, self::OTP_PURPOSE, $this->otp);

        if (! $result['success']) {
            $this->addError('otp', $result['message']);
            return;
        }

        session()->put(self::SESSION_VERIFIED, $this->phone);
        session()->forget([self::SESSION_SEND_FAILED, self::SESSION_SKIPPED]);

        return $this->update();
    }

    /**
     * Lewati verifikasi (nomor tetap tersimpan, tapi tercatat "belum terverifikasi"
     * di AuthLog). Hanya diizinkan bila verifikasi tidak mungkin dilakukan.
     */
    public function skipVerification()
    {
        if (! $this->needsPhoneSetup || $this->step !== 2 || ! $this->canSkipVerification()) {
            return;
        }

        $this->normalizePhone();
        $this->validate($this->phoneRules(), $this->messages());

        session()->put(self::SESSION_SKIPPED, $this->phone);

        return $this->update();
    }

    public function backStep(): void
    {
        // Sedang di tahap input OTP -> kembali ke input nomor (untuk koreksi nomor).
        if ($this->step === 2 && $this->otpSent) {
            $this->resetPhoneVerification();
            return;
        }

        $this->resetPhoneVerification();
        $this->step = 1;
    }

    public function update()
    {
        $this->normalizePhone();
        $this->validate();

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // GERBANG VERIFIKASI: nomor WA baru hanya boleh disimpan bila sudah
        // dibuktikan lewat OTP untuk nomor yang PERSIS sama, atau verifikasi
        // sengaja dilewati saat memang tidak mungkin dilakukan. Dicek dari
        // session server, jadi tidak bisa dilewati dengan memanipulasi client.
        $phoneVerified = false;
        if ($this->needsPhoneSetup) {
            $phoneVerified = session(self::SESSION_VERIFIED) === $this->phone;
            $skipped       = session(self::SESSION_SKIPPED) === $this->phone && $this->canSkipVerification();

            if (! $phoneVerified && ! $skipped) {
                $this->step = 2;
                $this->otpSent = false;
                $this->addError('phone', 'Nomor WhatsApp belum diverifikasi. Kirim kode verifikasi terlebih dahulu.');
                return;
            }
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

        // Bukti verifikasi sudah terpakai -- bersihkan supaya tidak menempel di sesi.
        $this->resetPhoneVerification();

        AuthLog::log(
            'password.changed',
            $user->id,
            $user->email,
            $this->needsPhoneSetup
                ? ($phoneVerified
                    ? 'Password berhasil diubah & nomor WhatsApp aktif berhasil didaftarkan dan diverifikasi via OTP (login pertama)'
                    : 'Password berhasil diubah & nomor WhatsApp didaftarkan TANPA verifikasi OTP (dilewati karena pengiriman WA tidak tersedia) (login pertama)')
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
        return view('livewire.password.change-password', [
            'canSkipVerification' => $this->needsPhoneSetup && $this->step === 2 && $this->canSkipVerification(),
        ]);
    }
}