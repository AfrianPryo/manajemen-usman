<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            // Jenis dokumen: finance_report, surat_keterangan, berita_acara_aset, laporan_konsolidasi
            $table->string('type');
            $table->string('name');
            $table->text('description')->nullable();
            // Path file .docx yang berisi placeholder ${...}, di-upload Master Admin
            $table->string('file_path');
            // Dokumentasi placeholder yang tersedia untuk jenis ini (untuk ditampilkan di UI)
            $table->json('placeholders')->nullable();
            // Format penomoran surat, contoh: {nomor}/UN/TEFA/{bulan_romawi}/{tahun}
            $table->string('numbering_format')->default('{nomor}/UN/TEFA/{bulan_romawi}/{tahun}');
            $table->enum('numbering_reset', ['yearly', 'monthly', 'never'])->default('yearly');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
