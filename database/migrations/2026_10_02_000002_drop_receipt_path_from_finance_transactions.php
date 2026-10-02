<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-08 Fix: Kolom 'receipt_path' ditambahkan oleh migration lama
 * (2026_08_16_093334_add_receipt_path_to_transactions_table) tapi tidak
 * pernah dipakai — seluruh kode (Model fillable, Livewire, Export) memakai
 * kolom 'proof_file' yang sudah ada sejak create_finance_transactions_table.
 * Kolom receipt_path ini mubazir dan membingungkan, jadi dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('finance_transactions', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('finance_transactions', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('description');
            }
        });
    }
};
