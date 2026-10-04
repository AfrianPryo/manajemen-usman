<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_logs', function (Blueprint $table) {
            $table->id();
            // Di-index eksplisit: dipakai eager load with('user') & filter per user.
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            // login.success, login.failed, logout, access.forbidden, password.changed
            $table->string('event');
            $table->string('identifier')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['event', 'created_at']);
            $table->index('created_at', 'auth_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_logs');
    }
};
