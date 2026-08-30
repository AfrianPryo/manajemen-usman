<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk modul "Manajemen Pelanggan" (lihat App\Models\Customer).
 * unit_id WAJIB (setiap pelanggan tercatat milik satu Unit Usaha, sama
 * seperti ServiceOrder) -- sisi Master melihat gabungan semua unit lewat
 * relasi ini, sisi Unit Admin dikunci ke unit_id-nya sendiri lewat trait
 * ScopedToUnit, persis pola Unit\ServiceOrder\Index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('category', ['baru', 'reguler', 'member', 'vip'])->default('baru');
            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedInteger('total_visits')->default(0);
            $table->dateTime('last_visit_at')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['unit_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
