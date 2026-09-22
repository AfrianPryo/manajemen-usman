<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel daftar blokir akses (fitur "Keamanan" di sisi Master Admin,
 * pengganti/tambahan dari menu "Aktivitas" -- lihat App\Livewire\Master\
 * Activities\Index & resources/views/livewire/master/activities/index.blade.php).
 *
 * Menyimpan alamat IP atau perangkat (diidentifikasi lewat User-Agent,
 * karena aplikasi ini tidak punya device-fingerprinting terpisah) yang
 * diblokir Master Admin secara manual dari histori Log Aktivitas Login
 * (tabel `auth_logs`). Selama baris masih ada di tabel ini, IP/perangkat
 * terkait ditolak untuk login (lihat App\Livewire\Auth\Login::login())
 * dan langsung di-logout paksa kalau masih ada sesi aktif (lihat
 * App\Http\Middleware\EnsureUserIsActive, middleware 'user.active' yang
 * sudah berjalan di seluruh route terautentikasi).
 *
 * Baris DIHAPUS (bukan soft-delete/flag) saat Master Admin membuka blokir
 * -- riwayat blokir/buka-blokir tetap tercatat lewat AuditLog::record()
 * di App\Livewire\Master\Activities\Index (event SECURITY_BLOCK_CREATED /
 * SECURITY_BLOCK_REMOVED), jadi tidak ada data yang benar-benar hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_accesses', function (Blueprint $table) {
            $table->id();
            // 'ip'     -> $value berisi alamat IP (mis. 103.10.20.30)
            // 'device' -> $value berisi string User-Agent perangkat/browser
            $table->string('type', 10);
            $table->string('value');
            $table->string('reason')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu entri unik per kombinasi tipe+nilai supaya tidak ada
            // duplikasi blokir untuk IP/perangkat yang sama.
            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_accesses');
    }
};
