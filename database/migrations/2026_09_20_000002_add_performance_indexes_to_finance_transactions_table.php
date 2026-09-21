<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks tambahan untuk tabel `finance_transactions` (audit performa poin 2).
 *
 * LATAR BELAKANG
 * --------------
 * Halaman Dashboard Master, Statistik Usaha (Master & Unit), dan Ekspor
 * menjalankan agregasi berat dengan pola filter yang SELALU sama:
 *
 *     WHERE type = 'income'|'expense'
 *       AND status = 'completed'
 *       AND transaction_date BETWEEN ? AND ?
 *     [AND unit_id = ?]
 *     GROUP BY unit_id / DATE(transaction_date)
 *
 * Indeks yang sudah ada dari migration awal:
 *   - (unit_id, status, transaction_date)  -> tidak mencakup kolom `type`
 *   - (type, status)                       -> tidak mencakup `transaction_date`
 *
 * Akibatnya, untuk query "seluruh unit" di atas, MySQL hanya bisa memakai
 * (type, status) lalu tetap harus memindai SEMUA baris yang cocok untuk
 * menyaring rentang tanggalnya. Saat tabel mencapai ratusan ribu baris,
 * inilah yang membuat load awal (sebelum masuk cache) terasa lama dan
 * memblokir I/O database.
 *
 * YANG DITAMBAHKAN
 * ----------------
 *   - (type, status, transaction_date)
 *       Untuk agregasi lintas unit: Dashboard Master, grafik arus kas,
 *       kontribusi omzet per unit.
 *
 *   - (unit_id, type, status, transaction_date)
 *       Untuk agregasi yang di-scope ke satu unit: Dashboard Unit,
 *       Statistik Usaha saat filter unit dipilih.
 *
 * CATATAN KEAMANAN MIGRASI
 * ------------------------
 * Migration ini hanya MENAMBAH indeks, tidak pernah menghapus/mengubah
 * kolom, sehingga tidak ada risiko kehilangan data. Indeks lama
 * (type, status) sengaja TIDAK di-drop walaupun kini menjadi prefix dari
 * indeks baru: men-drop indeks mengandalkan nama yang dibuat otomatis
 * oleh Laravel, dan kalau nama itu berbeda di database yang sudah berjalan,
 * migration akan gagal di tengah jalan. Biaya redundansi satu indeks jauh
 * lebih murah daripada risiko itu.
 *
 * Setiap penambahan dijaga dengan pengecekan "sudah ada atau belum", supaya
 * migration aman dijalankan ulang dan aman di database yang sudah pernah
 * ditambahi indeks ini secara manual.
 */
return new class extends Migration
{
    /**
     * Nama indeks ditulis eksplisit (bukan dibiarkan auto-generate) supaya
     * bisa dicek keberadaannya dan di-drop dengan pasti saat rollback.
     */
    private const INDEXES = [
        'ft_type_status_date_idx'      => ['type', 'status', 'transaction_date'],
        'ft_unit_type_status_date_idx' => ['unit_id', 'type', 'status', 'transaction_date'],
    ];

    public function up(): void
    {
        if (! Schema::hasTable('finance_transactions')) {
            return;
        }

        foreach (self::INDEXES as $name => $columns) {
            if ($this->indexExists($name)) {
                continue;
            }

            Schema::table('finance_transactions', function (Blueprint $table) use ($name, $columns) {
                $table->index($columns, $name);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('finance_transactions')) {
            return;
        }

        foreach (array_keys(self::INDEXES) as $name) {
            if (! $this->indexExists($name)) {
                continue;
            }

            Schema::table('finance_transactions', function (Blueprint $table) use ($name) {
                $table->dropIndex($name);
            });
        }
    }

    /**
     * Cek keberadaan indeks lewat Doctrine/Schema builder bawaan Laravel.
     * Dibungkus try/catch karena API-nya berbeda antar versi Laravel dan
     * antar driver (MySQL vs SQLite); kalau pengecekan tidak bisa dilakukan,
     * kita anggap indeks belum ada dan biarkan database sendiri yang menolak
     * duplikat -- perilaku ini tetap aman karena tidak menyentuh data.
     */
    private function indexExists(string $name): bool
    {
        try {
            // Schema adalah facade, jadi method_exists() harus diuji ke objek
            // Schema Builder di baliknya -- bukan ke kelas facade-nya.
            $builder = Schema::getFacadeRoot();

            if (method_exists($builder, 'hasIndex')) {
                // Laravel 11+
                return Schema::hasIndex('finance_transactions', $name);
            }

            if (method_exists($builder, 'getIndexes')) {
                // Laravel 10.x: kembalikan daftar indeks lalu cocokkan namanya.
                foreach (Schema::getIndexes('finance_transactions') as $index) {
                    if (strtolower($index['name'] ?? '') === strtolower($name)) {
                        return true;
                    }
                }

                return false;
            }

            // Laravel lama (masih memakai doctrine/dbal).
            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes('finance_transactions');

            return array_key_exists(strtolower($name), array_change_key_case($indexes, CASE_LOWER));
        } catch (\Throwable $e) {
            return false;
        }
    }
};
