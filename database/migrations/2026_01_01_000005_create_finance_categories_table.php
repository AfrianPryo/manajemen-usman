<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kategori transaksi keuangan, dengan 'scope':
 *   - 'all'      => berlaku untuk SEMUA Unit Usaha (termasuk yang dibuat
 *                   belakangan); pivot finance_category_unit TIDAK dipakai.
 *   - 'specific' => hanya untuk unit-unit di pivot finance_category_unit.
 * Lihat App\Models\FinanceCategory::scopeForUnit().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['income', 'expense']);
            $table->enum('scope', ['all', 'specific'])->default('specific');
            $table->timestamps();
        });

        // Diabaikan sepenuhnya kalau kategori induknya berscope 'all'.
        Schema::create('finance_category_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['finance_category_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_category_unit');
        Schema::dropIfExists('finance_categories');
    }
};
