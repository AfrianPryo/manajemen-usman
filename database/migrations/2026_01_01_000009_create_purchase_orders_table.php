<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul "Pembelian" (Purchase Order ke Vendor/Supplier).
 *
 * 'items' disimpan sebagai JSON (bukan tabel purchase_order_items) berisi
 * baris pembelian: product_id (nullable), name, qty, unit_price, subtotal.
 * Item tanpa product_id tetap sah (mis. beli jasa/utilitas) -- hanya ikut
 * ke total FinanceTransaction, tanpa StockMovement.
 *
 * Pembelian dibatalkan lewat status 'cancelled' (bukan dihapus) supaya
 * jejak audit tetap ada -- lihat cancelPurchase() di komponen Unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->index()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->string('po_number')->unique();
            $table->string('status')->default('completed'); // completed, cancelled
            $table->string('payment_method')->default('cash'); // cash, transfer, qris, dll (selaras finance_transactions)
            $table->json('items');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamps();

            $table->index(['unit_id', 'status']);
            // Total belanja bulan berjalan & daftar pembelian urut tanggal.
            $table->index(['status', 'purchased_at'], 'po_status_purchased_at_idx');
            $table->index(['unit_id', 'purchased_at'], 'po_unit_purchased_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
