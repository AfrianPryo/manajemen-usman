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
        // menimpa akun yang sudah ada), dan kategori transaksi default.
        $this->call([
            RoleSeeder::class,
            MasterAdminSeeder::class,
            FinanceCategorySeeder::class,
        ]);

        // 🟢 Data DUMMY/CONTOH (5 Unit Usaha contoh + admin unit masing-
        // masing dengan password statis & TANPA nomor WA) HANYA dijalankan
        // di local/testing. Di production, Unit Usaha & Admin Unit wajib
        // dibuat lewat alur aplikasi yang sebenarnya (Dashboard Master Admin
        // > Tambah Unit Usaha / Tambah Admin), yang MEWAJIBKAN nomor WA
        // aktif untuk notifikasi Fonnte -- sesuai alur tutorial setup awal
        // di App\Livewire\Master\Dashboard. Tanpa guard ini, data contoh
        // bisa ikut ter-seed ke production lewat `php artisan db:seed`.
        if (app()->environment('local', 'testing')) {
            $this->call([
                UnitSeeder::class,
                UnitAdminSeeder::class,
            ]);
        }
    }
}