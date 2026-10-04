<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('asset_tag')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available');
            $table->enum('condition', ['good', 'fair', 'damaged'])->default('good');
            $table->string('assigned_to')->nullable();
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // syncAllAssetNotifications(): aset yang statusnya/kondisinya alert-worthy.
            $table->index('status');
            $table->index('condition');
            $table->index('category', 'assets_category_idx');
            $table->index(['unit_id', 'status'], 'assets_unit_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
