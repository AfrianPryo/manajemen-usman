<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun pengguna + sesi login.
 *
 * Tabel `password_reset_tokens` bawaan Laravel SENGAJA tidak dibuat: aplikasi
 * ini tidak memakai password broker (reset password dilakukan lewat
 * permintaan ke Master Admin / OTP WhatsApp, bukan tautan email).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->foreignId('unit_id')->nullable()->index()->constrained('units')->nullOnDelete();

            // Informasi personel
            $table->string('nip')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('employee_status')->default('nip'); // nip, non_nip
            $table->string('profile_photo_path', 2048)->nullable();

            // Status akun & keamanan
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(true);

            // Tutorial/onboarding.
            //  - onboarding_completed_at: NULL = tutorial setup awal Dashboard Master
            //    belum pernah ditutup. (MasterAdminSeeder sengaja TIDAK mengisinya.)
            //  - completed_tours: daftar tutorial kontekstual (PageTours) yang sudah
            //    selesai/dilewati, mis. ["documents.generate", "documents.signature"].
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->json('completed_tours')->nullable();

            // Session check & logging
            $table->string('current_session_id')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
