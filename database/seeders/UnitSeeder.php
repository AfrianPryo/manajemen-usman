<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'TEFA', 'department' => 'PPLG', 'description' => 'Teaching Factory'],
            ['name' => 'Bengkel', 'department' => 'TO', 'description' => 'Unit Bengkel'],
            ['name' => 'Fotokopi', 'department' => 'MPLB', 'description' => 'Unit Fotokopi'],
            ['name' => 'Alfamart', 'department' => 'PM', 'description' => 'Unit Alfamart'],
            ['name' => 'Teh Siswa', 'department' => 'Akuntansi', 'description' => 'Unit Teh Siswa'],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['slug' => Str::slug($unit['name'])],
                [
                    'name' => $unit['name'],
                    'department' => $unit['department'],
                    'description' => $unit['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}