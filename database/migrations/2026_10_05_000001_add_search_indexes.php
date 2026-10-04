<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PERF-09 + PERF-13 + PERF-14: Tambah index pada kolom yang sering dipakai
 * untuk filter/pencarian tapi belum punya index.
 *
 * Tabel yang ditangani:
 *   - units.name                          (PERF-13: orderBy + filter nama unit)
 *   - vendors.name                        (PERF-09: pencarian & filter vendor)
 *   - finance_categories.name             (PERF-09: filter kategori transaksi)
 *   - recurring_transactions.title        (PERF-09: pencarian transaksi berulang)
 *   - official_documents.document_number  (PERF-14: cek duplikat + pencarian)
 *   - official_documents.title            (PERF-14: pencarian dokumen)
 *
 * Tabel yang SUDAH punya index dan tidak perlu ditambah:
 *   - products: sudah ada index ['unit_id','name'] dan index('stock')
 *   - customers: sudah ada index ['unit_id','name'] dan ['unit_id','category']
 *   - official_documents: sudah ada 1 index (kemungkinan unit_id FK)
 *   - assets: sudah ada 1 index (kemungkinan unit_id FK)
 */
return new class extends Migration
{
    public function up(): void
    {
        // units.name — dipakai di orderBy('name') di hampir semua dropdown
        if (Schema::hasTable('units') && ! $this->hasIndex('units', 'units_name_idx')) {
            Schema::table('units', function (Blueprint $table) {
                $table->index('name', 'units_name_idx');
            });
        }

        // vendors.name — pencarian vendor di Purchasing, filter di Transactions
        if (Schema::hasTable('vendors') && ! $this->hasIndex('vendors', 'vendors_name_idx')) {
            Schema::table('vendors', function (Blueprint $table) {
                $table->index('name', 'vendors_name_idx');
            });
        }

        // finance_categories.name — filter kategori di Transactions & Analytics
        if (Schema::hasTable('finance_categories') && ! $this->hasIndex('finance_categories', 'finance_categories_name_idx')) {
            Schema::table('finance_categories', function (Blueprint $table) {
                $table->index('name', 'finance_categories_name_idx');
            });
        }

        // recurring_transactions.title — pencarian di RecurringTransaction
        if (Schema::hasTable('recurring_transactions') && ! $this->hasIndex('recurring_transactions', 'recurring_transactions_title_idx')) {
            Schema::table('recurring_transactions', function (Blueprint $table) {
                $table->index('title', 'recurring_transactions_title_idx');
            });
        }

        // official_documents.document_number — cek duplikat + pencarian dokumen
        if (Schema::hasTable('official_documents') && ! $this->hasIndex('official_documents', 'official_documents_doc_number_idx')) {
            Schema::table('official_documents', function (Blueprint $table) {
                $table->index('document_number', 'official_documents_doc_number_idx');
            });
        }

        // official_documents.title — pencarian judul dokumen
        if (Schema::hasTable('official_documents') && ! $this->hasIndex('official_documents', 'official_documents_title_idx')) {
            Schema::table('official_documents', function (Blueprint $table) {
                $table->index('title', 'official_documents_title_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('units_name_idx');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_name_idx');
        });

        Schema::table('finance_categories', function (Blueprint $table) {
            $table->dropIndex('finance_categories_name_idx');
        });

        Schema::table('recurring_transactions', function (Blueprint $table) {
            $table->dropIndex('recurring_transactions_title_idx');
        });

        Schema::table('official_documents', function (Blueprint $table) {
            $table->dropIndex('official_documents_doc_number_idx');
            $table->dropIndex('official_documents_title_idx');
        });
    }

    /** Cek apakah index sudah ada (hindari duplicate index error). */
    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = \Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
        return count($indexes) > 0;
    }
};
