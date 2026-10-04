<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');                   // mis. PRODUCT_CREATED, STOCK_ADJUSTED, CATEGORY_DELETED
            $table->string('identifier')->nullable();  // kode/SKU/ID entitas (mis. PRD-001)
            $table->text('description')->nullable();   // penjelasan aksi
            $table->json('old_values')->nullable();    // snapshot sebelum diubah
            $table->json('new_values')->nullable();    // snapshot setelah diubah
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
            // ORDER BY created_at DESC, filter rentang tanggal, dan arsip berdasar retensi.
            $table->index('created_at', 'audit_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
