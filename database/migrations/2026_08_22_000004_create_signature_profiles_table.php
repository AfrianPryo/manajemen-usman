<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nama yang tercetak di bawah tanda tangan
            $table->string('position'); // Jabatan, mis. "Kepala TEFA"
            $table->string('signature_path')->nullable(); // gambar tanda tangan (idealnya PNG transparan)
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_profiles');
    }
};
