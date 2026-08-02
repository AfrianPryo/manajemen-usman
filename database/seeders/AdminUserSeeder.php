<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@sekolah.test'],
            ['name' => 'Administrator', 'password' => Hash::make('password')]
        );

        $admin->syncRoles(['admin']);
    }
}
