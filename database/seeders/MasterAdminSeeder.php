<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 🟢 PENTING: pakai firstOrCreate (bukan updateOrCreate seperti
        // sebelumnya) supaya blok kredensial "dari dev" di bawah HANYA
        // berlaku sekali, saat akun 'admin.master' benar-benar baru dibuat.
        // Kalau seeder ini dijalankan ulang di database yang sudah punya
        // akun ini (mis. re-deploy / `php artisan db:seed` lagi di
        // production), password & flag must_change_password TIDAK akan
        // ditimpa lagi -- mencegah admin yang sudah lama aktif jadi
        // terkunci ulang ke alur ganti password / password-nya kembali ke
        // password awal begitu saja.
        // Password awal TIDAK lagi tertulis di kode. Ambil dari
        // MASTER_ADMIN_PASSWORD di .env; bila kosong, dibuat acak dan
        // ditampilkan SEKALI di console saat akun benar-benar baru dibuat.
        $initialPassword = (string) env('MASTER_ADMIN_PASSWORD', '');
        $generated = $initialPassword === '';
        if ($generated) {
            $initialPassword = Str::password(16);
        }

        if (filter_var(env('MASTER_ADMIN_RESET', false), FILTER_VALIDATE_BOOLEAN)) {
            User::onlyTrashed()->where('username', 'admin.master')->first()?->restore();
        }

        $masterAdmin = User::firstOrCreate(
            ['username' => 'admin.master'], // 🟢 Disesuaikan menggunakan username login
            [
                'name'                 => 'Master Admin',
                'email'                => 'admin@sekolah.sch.id',
                'password'             => Hash::make($initialPassword),
                'unit_id'              => null,
                'is_active'            => true,
                // Akun Master Admin awal ini adalah kredensial "dari dev"
                // (username + password statis di atas), jadi WAJIB melalui
                // alur ganti password + setup nomor WA aktif pada login
                // pertama (lihat App\Livewire\Password\ChangePassword)
                // sebelum bisa masuk ke dashboard. 'phone' sengaja TIDAK
                // diisi di sini supaya ChangePassword::$needsPhoneSetup
                // otomatis aktif.
                'must_change_password' => true,
                // 🟢 Sengaja TIDAK diisi (biar tetap NULL) supaya
                // User::needsOnboarding() aktif untuk akun ini -- setelah
                // ganti password & setup nomor WA, Master Admin akan
                // disambut tutorial setup awal (tambah Unit Usaha, Admin,
                // integrasi Fonnte) SEKALI di Dashboard. Lihat
                // App\Livewire\Master\Dashboard.
                'onboarding_completed_at' => null,
            ]
        );

        if ($masterAdmin->wasRecentlyCreated && $generated) {
            $this->command?->warn("Akun Master Admin dibuat. Username: admin.master | Password sementara: {$initialPassword}");
            $this->command?->warn('Catat sekarang -- password ini tidak akan ditampilkan lagi dan wajib diganti saat login pertama.');
        }

        // RESET DARURAT (lupa password / akun lama sudah ada). Hanya aktif bila
        // MASTER_ADMIN_RESET=true DAN MASTER_ADMIN_PASSWORD diisi eksplisit.
        // Matikan lagi flag-nya setelah berhasil login.
        $resetRequested = filter_var(env('MASTER_ADMIN_RESET', false), FILTER_VALIDATE_BOOLEAN);

        if ($resetRequested && ! $generated && ! $masterAdmin->wasRecentlyCreated) {
            $masterAdmin->forceFill([
                'password'             => Hash::make($initialPassword),
                'must_change_password' => true,
                'is_active'            => true,
                'current_session_id'   => null,
            ])->save();

            $this->command?->warn('Password admin.master DIRESET sesuai MASTER_ADMIN_PASSWORD. Wajib diganti saat login. Segera hapus MASTER_ADMIN_RESET dari .env.');
        } elseif ($resetRequested && $generated) {
            $this->command?->warn('MASTER_ADMIN_RESET diabaikan: isi MASTER_ADMIN_PASSWORD dulu.');
        }

        $masterAdmin->assignRole('master-admin');
    }
}