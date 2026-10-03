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

        $masterAdmin->assignRole('master-admin');
    }
}