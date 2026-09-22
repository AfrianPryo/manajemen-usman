<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai kapan sebuah akun sudah "menyelesaikan" (atau melewati)
     * tur/tutorial setup awal yang tampil sekali di Dashboard Master Admin
     * (menambahkan Unit Usaha pertama, Admin pertama, integrasi Fonnte,
     * dst -- lihat App\Livewire\Master\Dashboard).
     *
     * NULL  = tutorial BELUM pernah ditutup -> akan ditampilkan.
     * NOT NULL = tutorial sudah ditutup/selesai -> tidak ditampilkan lagi.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('onboarding_completed_at')->nullable()->after('must_change_password');
        });

        // 🟢 PENTING: backfill semua akun yang SUDAH ADA (dibuat sebelum
        // kolom ini ada) supaya langsung dianggap "selesai" dan tidak
        // tiba-tiba melihat tutorial setup awal saat migrasi ini
        // dijalankan di database yang sudah lama berjalan (mis. re-deploy
        // di production). Hanya akun BARU yang dibuat SETELAH migrasi ini
        // (khususnya Master Admin awal dari MasterAdminSeeder, yang
        // sengaja TIDAK mengisi kolom ini) yang akan bernilai NULL dan
        // melihat tutorialnya.
        DB::table('users')->whereNull('onboarding_completed_at')->update([
            'onboarding_completed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_completed_at');
        });
    }
};