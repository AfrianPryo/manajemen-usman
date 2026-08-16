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
            $table->string('event'); // Contoh: 'PRODUCT_CREATED', 'STOCK_ADJUSTED', 'CATEGORY_DELETED'
            $table->string('identifier')->nullable(); // Kode/SKU/ID entitas (misal: 'PRD-001')
            $table->text('description')->nullable(); // Penjelasan aksi
            $table->json('old_values')->nullable(); // Snapshot data sebelum diubah
            $table->json('new_values')->nullable(); // Snapshot data setelah diubah
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};