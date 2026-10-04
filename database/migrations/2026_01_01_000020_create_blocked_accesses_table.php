<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daftar blokir akses (fitur "Keamanan" Master Admin): IP atau perangkat
 * (User-Agent) yang diblokir manual dari histori `auth_logs`. Selama baris
 * ada, IP/perangkat itu ditolak login (Auth\Login) dan di-logout paksa bila
 * masih punya sesi (middleware EnsureUserIsActive). Baris DIHAPUS saat
 * dibuka blokirnya; riwayatnya tetap tercatat lewat AuditLog.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_accesses', function (Blueprint $table) {
            $table->id();
            // 'ip' => value = alamat IP | 'device' => value = string User-Agent
            $table->string('type', 10);
            $table->string('value');
            $table->string('reason')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_accesses');
    }
};
