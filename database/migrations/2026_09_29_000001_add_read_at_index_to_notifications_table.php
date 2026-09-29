<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks untuk query lonceng notifikasi (App\Livewire\NotificationSidebar):
 *
 *   WHERE notifiable_type = ? AND notifiable_id = ? AND read_at IS NULL
 *   ORDER BY created_at DESC LIMIT 10
 *
 * Indeks morphs() bawaan hanya mencakup (notifiable_type, notifiable_id), dan
 * indeks (notifiable_type, type, read_at) tidak bisa dipakai karena kolom
 * `type` tidak ikut difilter. Migration ini HANYA MENAMBAH indeks (tidak ada
 * data/kolom yang diubah), dijaga pengecekan "tabel & kolom ada" + "indeks
 * belum ada", sehingga aman dijalankan ulang.
 */
return new class extends Migration
{
    private const TABLE = 'notifications';
    private const INDEX = 'notifications_notifiable_read_at_idx';
    private const COLUMNS = ['notifiable_type', 'notifiable_id', 'read_at'];

    public function up(): void
    {
        if (! Schema::hasTable(self::TABLE)
            || ! Schema::hasColumns(self::TABLE, self::COLUMNS)
            || $this->indexExists()) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->index(self::COLUMNS, self::INDEX);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable(self::TABLE) || ! $this->indexExists()) {
            return;
        }

        Schema::table(self::TABLE, function (Blueprint $table) {
            $table->dropIndex(self::INDEX);
        });
    }

    /** Sama seperti pola di migration 2026_09_24_000001. */
    private function indexExists(): bool
    {
        try {
            $builder = Schema::getFacadeRoot();

            if (method_exists($builder, 'hasIndex')) {
                return Schema::hasIndex(self::TABLE, self::INDEX);
            }

            if (method_exists($builder, 'getIndexes')) {
                foreach (Schema::getIndexes(self::TABLE) as $index) {
                    if (strtolower($index['name'] ?? '') === strtolower(self::INDEX)) {
                        return true;
                    }
                }

                return false;
            }

            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes(self::TABLE);

            return array_key_exists(strtolower(self::INDEX), array_change_key_case($indexes, CASE_LOWER));
        } catch (\Throwable $e) {
            return false;
        }
    }
};
