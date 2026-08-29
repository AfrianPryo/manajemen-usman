<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
|--------------------------------------------------------------------------
| Tabel: service_orders
|--------------------------------------------------------------------------
| Menyimpan data pesanan/order jasa untuk unit usaha berkategori "jasa"
| (lihat kolom units.category, diatur lewat form Tambah/Edit Unit di
| Master > Unit Usaha). Konsepnya sejajar dengan "Product" milik unit
| ritel: Product = barang yang dijual unit ritel, ServiceOrder = jasa
| yang dipesan/dikerjakan unit jasa.
|
| Modul ini SENGAJA dibuat berdiri sendiri (tidak reuse tabel/relasi
| Product atau Category milik ritel) supaya unit ritel maupun jasa bisa
| berkembang independen tanpa saling mempengaruhi skema data.
*/
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();

            // Unit usaha (kategori 'jasa') pemilik pesanan ini.
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();

            // Admin unit yang mencatat/menangani pesanan (opsional).
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('customer_name', 150);
            $table->string('customer_phone', 30)->nullable();

            // Nama layanan yang dipesan, contoh: "Servis AC", "Cukur Rambut", "Reparasi Elektronik".
            $table->string('service_name', 150);
            $table->text('description')->nullable();

            // Nama petugas/teknisi yang ditugaskan (bebas teks, sejalan dengan
            // pola pic_name di tabel units -- tidak dibuat FK supaya modul ini
            // tetap ringan dan tidak menambah dependensi ke tabel users lain).
            $table->string('assigned_to', 100)->nullable();

            $table->decimal('price', 15, 2)->default(0);

            // Jadwal pengerjaan / kunjungan ke pelanggan.
            $table->dateTime('scheduled_at')->nullable();

            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->default('pending');

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
