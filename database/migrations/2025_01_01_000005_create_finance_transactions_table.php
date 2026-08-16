<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_no')->nullable();
            $table->enum('type', ['income', 'expense']);
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            $table->string('payment_method')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->string('proof_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
