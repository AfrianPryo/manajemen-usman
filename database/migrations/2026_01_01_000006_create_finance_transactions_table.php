<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transaksi keuangan. Dashboard, Statistik Usaha, dan Ekspor selalu memakai pola:
 *   WHERE type = ? AND status = 'completed' AND transaction_date BETWEEN ? AND ?
 *   [AND unit_id = ?]  GROUP BY unit_id / DATE(transaction_date)
 * sehingga indeks di bawah disusun untuk pola itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finance_category_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->string('reference_no')->nullable();
            $table->enum('type', ['income', 'expense']);
            $table->enum('status', ['completed', 'pending', 'cancelled'])->default('completed');
            $table->string('payment_method')->nullable();
            // decimal(15,2) agar konsisten dengan cast 'decimal:2' di model
            // (nilai desimal seperti 1500.50 tidak terpotong).
            $table->decimal('amount', 15, 2)->unsigned();
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->string('proof_file')->nullable();
            $table->timestamps();

            // Daftar/laporan per unit + status dalam rentang tanggal.
            $table->index(['unit_id', 'status', 'transaction_date']);
            // Agregasi lintas unit (Dashboard Master, arus kas, kontribusi omzet).
            $table->index(['type', 'status', 'transaction_date'], 'ft_type_status_date_idx');
            // Agregasi yang di-scope ke satu unit (Dashboard Unit, Statistik Usaha).
            $table->index(['unit_id', 'type', 'status', 'transaction_date'], 'ft_unit_type_status_date_idx');
            // ORDER BY tanggal / filter rentang tanggal tanpa filter tipe/status.
            $table->index('transaction_date', 'ft_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
