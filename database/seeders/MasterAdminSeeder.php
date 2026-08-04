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
            ['email' => 'admin@sekolah.sch.id'],
            [
                'name' => 'Master Admin',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        $masterAdmin->assignRole('master-admin');
    }
}