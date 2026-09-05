<?php

namespace Database\Seeders;

use App\Models\FinanceCategory;
use Illuminate\Database\Seeder;

class FinanceCategorySeeder extends Seeder
{
    /**
     * PERUBAHAN: kategori transaksi dulunya di-duplikasi per Unit Usaha
     * (satu baris per unit x per kategori). Sekarang kategori bisa
     * berscope 'all' (otomatis berlaku ke SEMUA unit, termasuk yang
     * dibuat belakangan) -- jadi dummy data default cukup 1 baris per
     * nama kategori, tidak perlu di-loop per Unit lagi. Admin (Master
     * ataupun Unit) tetap bisa menambah kategori baru yang scope-nya
     * 'specific' (khusus beberapa unit saja) lewat menu "Kelola Kategori"
     * di dalam Transaksi -- lihat App\Livewire\Master\Transactions\Index.
     */
    public function run(): void
    {
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
            FinanceCategory::firstOrCreate(
                ['name' => $cat['name'], 'type' => $cat['type']],
                ['scope' => 'all']
            );
        }
    }
}
