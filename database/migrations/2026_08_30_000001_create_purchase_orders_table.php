<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk modul "Pembelian" (Purchase Order ke Vendor/Supplier).
 *
 * Menjembatani gap yang sebelumnya ada antara App\Models\Vendor dan
 * App\Models\FinanceTransaction (tidak ada kolom vendor_id sama sekali di
 * finance_transactions) -- lihat catatan di App\Livewire\Unit\Purchasing\Index.
 *
 * 'items' disimpan sebagai JSON (bukan tabel purchase_order_items terpisah)
 * berisi array baris pembelian: product_id (nullable, untuk item yang
 * memang produk bertsok), name, qty, unit_price, subtotal. Item TANPA
 * product_id tetap sah (mis. beli jasa/utilitas dari vendor) -- baris itu
 * hanya berkontribusi ke total FinanceTransaction, tanpa StockMovement.
 *
 * 'finance_transaction_id' & status memungkinkan pembelian DIBATALKAN
 * (status 'cancelled') dengan tetap menyisakan jejak audit yang jelas,
 * bukan dihapus begitu saja -- lihat cancelPurchase() di komponen Unit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
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
            $table->index(['vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
