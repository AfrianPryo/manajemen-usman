<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel riwayat "Pengumuman" yang dikirim Master Admin ke seluruh Unit
 * Admin. Pengiriman aktualnya tetap memakai infrastruktur notifikasi yang
 * sudah ada (App\Notifications\SystemNotification, kolom database di tabel
 * `notifications` bawaan Laravel) -- tabel ini HANYA menyimpan jejak/
 * riwayat pengumuman itu sendiri (siapa mengirim, kapan, ke berapa admin),
 * supaya Master Admin bisa melihat kembali pengumuman apa saja yang pernah
 * dikirim, mirip halaman "Riwayat" pada modul Dokumen Resmi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('badge')->default('Pengumuman');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
