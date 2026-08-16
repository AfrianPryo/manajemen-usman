<?php

namespace Database\Seeders;

use App\Models\FinanceCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class FinanceCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua unit usaha yang ada
        $units = Unit::all();

        foreach ($units as $unit) {
            $categories = [
                // Pemasukan
                ['name' => 'Penjualan Produk', 'type' => 'income'],
                ['name' => 'Pendapatan Jasa', 'type' => 'income'],
                ['name' => 'Pemasukan Lain-lain', 'type' => 'income'],

                // Pengeluaran
                ['name' => 'Pembelian Stok & Bahan', 'type' => 'expense'],
                ['name' => 'Biaya Operasional & Listrik', 'type' => 'expense'],
                ['name' => 'Gaji & Honorarium', 'type' => 'expense'],
                ['name' => 'Pengeluaran Lain-lain', 'type' => 'expense'],
            ];

            foreach ($categories as $cat) {
                FinanceCategory::firstOrCreate([
                    'unit_id' => $unit->id,
                    'name'    => $cat['name'],
                    'type'    => $cat['type'],
                ]);
            }
        }
    }
}