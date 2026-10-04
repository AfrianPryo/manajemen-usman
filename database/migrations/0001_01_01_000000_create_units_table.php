<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unit Usaha. Dibuat PALING AWAL karena users.unit_id (dan hampir semua
 * tabel domain) bergantung padanya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('slug')->unique();
            $table->string('department'); // PPLG, TO, MPLB, PM, Akuntansi
            $table->enum('category', ['ritel', 'jasa'])->default('ritel'); // Ritel (produk/toko) | Jasa
            $table->string('pic_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
