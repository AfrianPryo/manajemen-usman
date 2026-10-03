<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPT-08: Tambah explicit index pada auth_logs.user_id
 *   — FK ke users sudah ada, tapi MySQL tidak selalu membuat implicit index
 *     untuk FK nullable. Kolom ini dipakai di eager load `with('user')` dan
 *     filter pencarian per user.
 *
 * OPT-11: Tambah explicit index pada finance_transactions.finance_category_id
 *   dan finance_transactions.user_id
 *   — Keduanya adalah FK yang dipakai sebagai filter query analitik. FK
 *     constraint di MySQL InnoDB memang membuat implicit index, tapi index
 *     eksplisit menjamin keberadaannya dan muncul di EXPLAIN output.
 *
 * OPT-18: Tambah explicit index pada users.unit_id
 *   — Dipakai di hampir setiap middleware Unit dan komponen Unit untuk
 *     $user->unit eager load. FK nullable, perlu index eksplisit.
 *
 * Semua penambahan dijaga dengan pengecekan kolom & index agar aman
 * dijalankan ulang (idempotent).
 */
return new class extends Migration
{
    private const INDEXES = [
        'auth_logs' => [
            'auth_logs_user_id_idx' => ['user_id'],
        ],
        'finance_transactions' => [
            'ft_category_id_idx' => ['finance_category_id'],
            'ft_user_id_idx'     => ['user_id'],
        ],
        'users' => [
            'users_unit_id_idx' => ['unit_id'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($indexes as $name => $columns) {
                if (! Schema::hasColumns($table, $columns) || $this->indexExists($table, $name)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($name, $columns) {
                    $blueprint->index($columns, $name);
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach (array_keys($indexes) as $name) {
                if (! $this->indexExists($table, $name)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($name) {
                    $blueprint->dropIndex($name);
                });
            }
        }
    }

    private function indexExists(string $table, string $name): bool
    {
        try {
            $builder = Schema::getFacadeRoot();

            if (method_exists($builder, 'hasIndex')) {
                return Schema::hasIndex($table, $name);
            }

            if (method_exists($builder, 'getIndexes')) {
                foreach (Schema::getIndexes($table) as $index) {
                    if (strtolower($index['name'] ?? '') === strtolower($name)) {
                        return true;
                    }
                }

                return false;
            }

            $indexes = Schema::getConnection()
                ->getDoctrineSchemaManager()
                ->listTableIndexes($table);

            return array_key_exists(strtolower($name), array_change_key_case($indexes, CASE_LOWER));
        } catch (\Throwable) {
            return false;
        }
    }
};

