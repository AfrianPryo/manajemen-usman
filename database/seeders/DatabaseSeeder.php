<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 🟢 Data WAJIB & aman dijalankan di environment manapun (termasuk
        // production): role/permission sistem, akun Master Admin awal
        // "dari dev" (lihat MasterAdminSeeder -- hanya sekali, tidak
        // menimpa akun yang sudah ada), kategori transaksi default, dan
        // pengaturan Fonnte awal dari .env (lihat FonnteSettingsSeeder --
        // hanya mengisi yang kosong, tidak menimpa perubahan dari
        // menu Pengaturan).
        $this->call([
            RoleSeeder::class,
            MasterAdminSeeder::class,
            FinanceCategorySeeder::class,
            FonnteSettingsSeeder::class,
        ]);

        // 🟢 Data DUMMY/CONTOH (5 Unit Usaha contoh) HANYA dijalankan di
        // local/testing. Admin Unit TIDAK di-seed: Admin Unit wajib dibuat
        // lewat alur aplikasi yang sebenarnya (Dashboard Master Admin >
        // Tambah Admin), yang MEWAJIBKAN nomor WA aktif untuk notifikasi
        // Fonnte -- sesuai alur tutorial setup awal di
        // App\Livewire\Master\Dashboard. Tanpa guard ini, data contoh
        // bisa ikut ter-seed ke production lewat `php artisan db:seed`.
        if (app()->environment('local', 'testing')) {
            $this->call([
                UnitSeeder::class,
            ]);
        }
    }
}