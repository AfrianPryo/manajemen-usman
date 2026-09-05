<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // PERUBAHAN: kategori transaksi TIDAK lagi terikat ke SATU unit_id
        // saja (kolom unit_id lama dibuang). Sekarang kategori punya
        // 'scope':
        //   - 'all'      => otomatis berlaku untuk SEMUA Unit Usaha
        //                   (termasuk unit yang dibuat belakangan), tabel
        //                   pivot finance_category_unit TIDAK dipakai sama
        //                   sekali untuk baris berscope ini.
        //   - 'specific' => custom, hanya berlaku untuk unit-unit tertentu
        //                   saja -- daftar unit-nya disimpan di tabel
        //                   pivot finance_category_unit di bawah.
        // Lihat App\Models\FinanceCategory::scopeForUnit() untuk query
        // gabungan keduanya, dan App\Livewire\Master\Transactions\Index
        // (menu "Kelola Kategori" di dalam Transaksi) untuk CRUD-nya.
        Schema::create('finance_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['income', 'expense']);
            $table->enum('scope', ['all', 'specific'])->default('specific');
            $table->timestamps();
        });

        // Pivot: unit-unit mana saja yang memakai kategori berscope
        // 'specific'. Baris di sini diabaikan sepenuhnya kalau kategori
        // induknya berscope 'all'.
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
