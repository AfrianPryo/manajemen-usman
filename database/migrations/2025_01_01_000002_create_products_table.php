<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('unit_type')->default('pcs');
            $table->unsignedBigInteger('purchase_price')->default(0);
            $table->unsignedBigInteger('selling_price')->default(0);
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            // Kode barang unik khusus per unit usaha
            $table->unique(['unit_id', 'code']);

            // Dipakai syncAllStockNotifications() untuk mencari produk yang
            // stoknya di bawah/​sama dengan ambang batas.
            $table->index('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};