<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Katalog file arsip log (Excel) yang dihasilkan oleh
        // App\Services\LogArchiveService. Satu baris = satu file arsip
        // (satu jenis log x satu bulan). Barisnya dibuat dalam transaksi
        // yang SAMA dengan penghapusan data lama dari tabel utama, jadi
        // kalau ada baris di sini berarti datanya memang sudah aman di file.
        Schema::create('log_archives', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);          // 'auth' (log login) | 'audit' (audit log sistem)
            $table->string('period', 7);         // 'YYYY-MM'
            $table->string('file_path');         // relatif terhadap disk 'local'
            $table->string('file_name');
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedBigInteger('file_size')->default(0); // byte
            $table->timestamps();                // created_at = waktu diarsipkan

            $table->index(['type', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_archives');
    }
};
