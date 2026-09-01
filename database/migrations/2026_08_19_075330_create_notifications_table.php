<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Menopang query batch di SyncsAlertNotifications
            // (idsWithPendingAlerts, batchFireAlert, batchClearAlert) yang
            // selalu memfilter notifiable_type + type + read_at SEBELUM
            // membaca kolom JSON `data`. Index morphs() bawaan hanya mencakup
            // (notifiable_type, notifiable_id), belum termasuk type/read_at.
            $table->index(['notifiable_type', 'type', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
