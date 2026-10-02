<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BUG-03 Fix: Kolom 'amount' di finance_transactions sebelumnya adalah
 * unsignedBigInteger (bilangan bulat) tapi Model meng-cast-nya sebagai
 * 'decimal:2'. Ini menyebabkan nilai desimal (mis. 1500.50) ditruncate
 * menjadi 1500 saat disimpan ke DB. Migrasi ini mengubah tipe kolom
 * menjadi decimal(15,2) agar konsisten dengan cast di model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->unsigned()->change();
        });
    }

    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });
    }
};
