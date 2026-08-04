<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'master-admin']);
        Role::updateOrCreate(['name' => 'unit-admin']);
    }
}