<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan daftar tutorial kontekstual (PageTours) yang sudah selesai/dilewati
 * per akun, mis. ["documents.generate", "documents.signature"]. Terpisah dari
 * 'onboarding_completed_at' (tutorial setup awal di Dashboard Master Admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('completed_tours')->nullable()->after('onboarding_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('completed_tours');
        });
    }
};
