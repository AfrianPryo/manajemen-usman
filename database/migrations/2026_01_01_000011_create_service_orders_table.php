<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan jasa untuk unit usaha berkategori "jasa" (units.category).
 * Sejajar dengan Product milik unit ritel, tetapi SENGAJA berdiri sendiri
 * (tidak memakai tabel Product/Category) agar kedua jenis unit berkembang
 * independen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            // Admin unit yang mencatat/menangani pesanan (opsional).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name', 150);
            $table->string('customer_phone', 30)->nullable();
            // Contoh: "Servis AC", "Cukur Rambut", "Reparasi Elektronik".
            $table->string('service_name', 150);
            $table->text('description')->nullable();
            // Petugas/teknisi: teks bebas (sejalan dengan units.pic_name), bukan FK.
            $table->string('assigned_to', 100)->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->dateTime('scheduled_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
