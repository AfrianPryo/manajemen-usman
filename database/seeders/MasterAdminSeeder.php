<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterAdminSeeder extends Seeder
{
    public function run(): void
    {
        $masterAdmin = User::updateOrCreate(
            ['username' => 'admin.master'], // 🟢 Disesuaikan menggunakan username login
            [
                'name'                 => 'Master Admin',
                'email'                => 'admin@sekolah.sch.id',
                'password'             => Hash::make('Password123!'),
                'unit_id'              => null,
                'is_active'            => true,
                'must_change_password' => false, // Master admin awal tidak perlu force pass change
            ]
        );

        $masterAdmin->assignRole('master-admin');
    }
}