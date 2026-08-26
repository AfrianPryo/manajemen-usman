<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('official_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->restrictOnDelete();
            $table->string('type');
            $table->string('document_number')->unique();
            $table->string('title');
            $table->string('subject')->nullable(); // Perihal
            $table->string('recipient')->nullable(); // Ditujukan kepada
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            // Snapshot lengkap data yang digunakan saat generate — untuk audit trail,
            // supaya nilai di dokumen tidak pernah "berubah sendiri" walau data sumber berubah.
            $table->json('data_snapshot');
            $table->string('file_path'); // hasil .docx yang sudah jadi
            $table->string('signed_by_name')->nullable();
            $table->string('signed_by_position')->nullable();
            $table->string('signature_path')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'generated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('official_documents');
    }
};
