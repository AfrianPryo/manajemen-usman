<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks tambahan (audit performa: "Database indexes").
 *
 * Pelengkap migration 2026_09_20_000002 (yang hanya menyentuh
 * finance_transactions). Semua indeks di sini HANYA MENAMBAH -- tidak ada
 * kolom/data yang diubah atau dihapus -- dan setiap penambahan dijaga
 * pengecekan "tabel & kolom ada" + "indeks belum ada", sehingga aman
 * dijalankan ulang dan aman di database yang sudah pernah diberi indeks
 * serupa secara manual.
 *
 * Pola query yang ditopang:
 *  - finance_transactions(transaction_date)  : ORDER BY tanggal di daftar
 *      transaksi tanpa filter tipe/status + filter rentang tanggal.
 *  - audit_logs / auth_logs (created_at)     : ORDER BY created_at DESC,
 *      filter rentang tanggal, dan penghapusan/arsip berdasar retensi.
 *  - products(unit_id, name)                 : daftar produk per unit urut nama.
 *  - customers(unit_id, category)            : KPI kategori pelanggan per unit.
 *  - assets(category), assets(unit_id,status): filter kategori & status per unit.
 *  - recurring_transactions(status, next_run_date): job harian recurring:process.
 *  - official_documents(generated_at)        : riwayat dokumen urut terbaru.
 *  - purchase_orders(status, purchased_at) & (unit_id, purchased_at):
 *      total belanja bulan berjalan & daftar pembelian urut tanggal.
 */
return new class extends Migration
{
    /** tabel => [nama_indeks => kolom[]] */
    private const INDEXES = [
        'finance_transactions' => [
            'ft_date_idx' => ['transaction_date'],
        ],
        'audit_logs' => [
            'audit_created_at_idx' => ['created_at'],
        ],
        'auth_logs' => [
            'auth_created_at_idx' => ['created_at'],
        ],
        'products' => [
            'products_unit_name_idx' => ['unit_id', 'name'],
        ],
        'customers' => [
            'customers_unit_category_idx' => ['unit_id', 'category'],
        ],
        'assets' => [
            'assets_category_idx'    => ['category'],
            'assets_unit_status_idx' => ['unit_id', 'status'],
        ],
        'recurring_transactions' => [
            'recurring_status_next_run_idx' => ['status', 'next_run_date'],
        ],
        'official_documents' => [
            'docs_generated_at_idx' => ['generated_at'],
        ],
        'purchase_orders' => [
            'po_status_purchased_at_idx' => ['status', 'purchased_at'],
            'po_unit_purchased_at_idx'   => ['unit_id', 'purchased_at'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (! Schema::hasColumns($tableName, $columns) || $this->indexExists($tableName, $name)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($name, $columns) {
                    $table->index($columns, $name);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (! $this->indexExists($tableName, $name)) {
                    continue;
                }

                Schema::table($tableName, function (Blueprint $table) use ($name) {
                    $table->dropIndex($name);
                });
            }
        }
    }

    /**
     * Cek indeks lewat Schema Builder bawaan Laravel (11+ / 10.x / lama).
     * Kalau pengecekan gagal, dianggap belum ada.
     */
    private function indexExists(string $tableName, string $name): bool
    {
        try {
            $builder = Schema::getFacadeRoot();

            if (method_exists($builder, 'hasIndex')) {
                return Schema::hasIndex($tableName, $name);
            }

            if (method_exists($builder, 'getIndexes')) {
                foreach (Schema::getIndexes($tableName) as $index) {
                    if (strtolower($index['name'] ?? '') === strtolower($name)) {
                        return true;
                    }
                }

                return false;
            }

            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes($tableName);

            return array_key_exists(strtolower($name), array_change_key_case($indexes, CASE_LOWER));
        } catch (\Throwable $e) {
            return false;
        }
    }
};
