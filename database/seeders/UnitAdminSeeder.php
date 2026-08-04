<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UnitAdminSeeder extends Seeder
{
    public function run(): void
    {
        $units = Unit::all();

        foreach ($units as $unit) {
            $username = 'admin.' . str_replace('-', '_', $unit->slug);

            $unitAdmin = User::updateOrCreate(
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