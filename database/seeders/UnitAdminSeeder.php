<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnitAdminSeeder extends Seeder
{
    // 🟢 Seeder ini HANYA dipanggil di local/testing (lihat DatabaseSeeder) --
    // dipakai supaya ada data admin unit contoh untuk development, BUKAN
    // untuk production (Admin Unit sungguhan wajib dibuat lewat Dashboard
    // Master Admin > Tambah Admin, yang mewajibkan nomor WA aktif untuk
    // Fonnte). 'phone' sengaja tidak diisi di sini.
    public function run(): void
    {
        $units = Unit::all();

        foreach ($units as $unit) {
            $username = 'admin.' . str_replace('-', '_', $unit->slug);

            // 🟢 Sama seperti MasterAdminSeeder: pakai firstOrCreate (bukan
            // updateOrCreate) supaya kredensial contoh ini HANYA berlaku
            // sekali saat akunnya benar-benar baru dibuat. Kalau seeder
            // ini dijalankan ulang di database lokal yang sudah punya
            // akun ini (mis. akun contoh itu sudah dipakai & ganti
            // password sendiri), password & must_change_password TIDAK
            // akan ditimpa ulang.
            $unitAdmin = User::firstOrCreate(
                ['username' => $username],
                [
                    'name'                 => 'Admin ' . $unit->name,
                    'email'                => $username . '@sekolah.sch.id',
                    'password'             => Hash::make('password123'),
                    'unit_id'              => $unit->id,
                    'is_active'            => true,
                    'must_change_password' => true, // Wajib ganti password pada login pertama
                ]
            );

            $unitAdmin->assignRole('unit-admin');
        }
    }
}