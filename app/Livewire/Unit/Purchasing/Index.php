<?php

namespace App\Livewire\Unit\Purchasing;

use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modul "Pembelian" -- menutup gap yang sebelumnya ada antara modul Vendor
 * & Supplier (buku alamat statis) dan pencatatan keuangan/stok: sebelum
 * ini, belanja ke vendor dicatat manual & terpisah lewat form Transaksi
 * Keuangan (tanpa tahu dibeli dari vendor mana) dan penyesuaian stok
 * (tanpa tahu itu hasil pembelian atau koreksi biasa).
 *
 * Alur: pilih vendor -> isi satu atau lebih baris item (boleh dari Produk
 * yang sudah ada di Inventaris, boleh juga item bebas seperti jasa/
 * utilitas yang tidak berpengaruh ke stok) -> submit -> otomatis membuat
 * SATU FinanceTransaction (expense, kategori "Pembelian") + StockMovement
 * (type 'in') untuk setiap baris yang terhubung ke Produk.
 *
 * Ditulis berdiri sendiri (bukan extends komponen Master, karena memang
 * belum ada modul serupa di sisi Master saat file ini dibuat -- lihat
 * App\Livewire\Master\Purchasing\Index yang HANYA rekap lintas-unit,
 * read-only, tanpa form Tambah) tapi memakai konvensi yang identik dengan
 * modul unit lain: WithPagination, trait ScopedToUnit, pola modal Tambah,
 * dan AuditLog::record() di setiap aksi tulis.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Pembelian')]
class Index extends Component
{
    use WithPagination, ScopedToUnit;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $showDetailModal = false;
    public ?PurchaseOrder $selectedPurchase = null;

    // Form Inputs
    public string $vendor_id = '';
    public string $payment_method = 'cash';
    public string $notes = '';

    /**
     * Baris item pembelian. Setiap baris: product_id (string, kosong kalau
     * item bebas non-produk), name (dipakai kalau product_id kosong, atau
     * sebagai fallback tampilan), qty, unit_price.
     */
    public array $items = [];

    public function mount(): void
    {
        $this->resetItems();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    private function resetItems(): void
    {
        $this->items = [
            ['product_id' => '', 'name' => '', 'qty' => 1, 'unit_price' => 0],
        ];
    }

    public function addItemRow(): void
    {
        $this->items[] = ['product_id' => '', 'name' => '', 'qty' => 1, 'unit_price' => 0];
    }

    public function removeItemRow(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    /**
     * Ketika baris item memilih Produk dari dropdown, isi otomatis nama &
     * harga beli dari Produk tersebut supaya user tidak perlu ketik ulang
     * (harga tetap bisa diedit manual kalau harga beli kali ini berbeda).
     */
    public function updatedItems($value, $key): void
    {
        if (!str_ends_with($key, '.product_id')) {
            return;
        }

        $index = explode('.', $key)[0];
        $productId = $this->items[$index]['product_id'] ?? null;

        if (!$productId) {
            return;
        }

        $product = Product::where('unit_id', $this->currentUnitId())->find($productId);

        if ($product) {
            $this->items[$index]['name'] = $product->name;
            if (empty($this->items[$index]['unit_price'])) {
                $this->items[$index]['unit_price'] = (string) $product->purchase_price;
            }
        }
    }

    protected function rules(): array
    {
        return [
            'vendor_id'                 => 'required|exists:vendors,id',
            'payment_method'            => 'required|in:cash,transfer,qris,lainnya',
            'notes'                     => 'nullable|string|max:1000',
            'items'                     => 'required|array|min:1',
            'items.*.product_id'        => 'nullable|exists:products,id',
            'items.*.name'              => 'required_without:items.*.product_id|nullable|string|max:191',
            'items.*.qty'               => 'required|numeric|min:0.01',
            'items.*.unit_price'        => 'required|numeric|min:0',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'items.*.name'       => 'nama item',
            'items.*.qty'        => 'jumlah',
            'items.*.unit_price' => 'harga satuan',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset(['vendor_id', 'notes']);
        $this->payment_method = 'cash';
        $this->resetItems();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function viewDetail(int $id): void
    {
        $this->selectedPurchase = PurchaseOrder::where('unit_id', $this->currentUnitId())
            ->with(['vendor', 'user', 'financeTransaction'])
            ->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedPurchase = null;
    }

    /**
     * Vendor yang boleh dipilih -- 'vendor' murni atau 'both' (vendor
     * SEKALIGUS supplier). Vendor dengan type 'supplier' saja sengaja
     * tetap ditampilkan juga, karena di lapangan pembatasan type vendor
     * vs supplier di modul Vendor & Supplier hanya label kategori, bukan
     * pembatasan fungsional -- Master Admin bebas mencatat tipe apa pun
     * sebagai sumber pembelian.
     */
    private function vendorOptions()
    {
        return Vendor::orderBy('name')->get();
    }

    private function productOptions()
    {
        return Product::where('unit_id', $this->currentUnitId())->orderBy('name')->get();
    }

    private function generatePoNumber(): string
    {
        do {
            $number = 'PO-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (PurchaseOrder::where('po_number', $number)->exists());

        return $number;
    }

    public function save(): void
    {
        $this->validate();

        $unitId = $this->currentUnitId();
        $vendor = Vendor::findOrFail($this->vendor_id);

        // Bersihkan & hitung total dari baris item yang valid saja (skip
        // baris kosong bila user menambah baris tapi tidak jadi diisi).
        $cleanItems = [];
        $total = 0;

        foreach ($this->items as $item) {
            $qty = (float) ($item['qty'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $productId = $item['product_id'] ?: null;
            $name = $item['name'] ?: null;

            if ($productId) {
                $product = Product::where('unit_id', $unitId)->find($productId);
                $name = $name ?: $product?->name;
            }

            if (!$name) {
                continue;
            }

            $subtotal = $qty * $price;
            $total += $subtotal;

            $cleanItems[] = [
                'product_id' => $productId ? (int) $productId : null,
                'name'       => $name,
                'qty'        => $qty,
                'unit_price' => $price,
                'subtotal'   => $subtotal,
            ];
        }

        if (empty($cleanItems)) {
            $this->addError('items', 'Minimal harus ada satu baris item pembelian yang valid.');
            return;
        }

        $poNumber = $this->generatePoNumber();

        DB::transaction(function () use ($unitId, $vendor, $cleanItems, $total, $poNumber) {
            $category = FinanceCategory::firstOrCreate(
                ['unit_id' => $unitId, 'name' => 'Pembelian', 'type' => 'expense'],
            );

            $transaction = FinanceTransaction::create([
                'unit_id'              => $unitId,
                'finance_category_id'  => $category->id,
                'user_id'              => auth()->id(),
                'reference_no'         => $poNumber,
                'type'                 => 'expense',
                'status'               => 'completed',
                'payment_method'       => $this->payment_method,
                'amount'               => $total,
                'description'          => "Pembelian ke vendor '{$vendor->name}' ({$poNumber})",
                'transaction_date'     => now(),
            ]);

            $purchase = PurchaseOrder::create([
                'unit_id'                => $unitId,
                'vendor_id'               => $vendor->id,
                'user_id'                 => auth()->id(),
                'finance_transaction_id'  => $transaction->id,
                'po_number'                => $poNumber,
                'status'                   => 'completed',
                'payment_method'           => $this->payment_method,
                'items'                    => $cleanItems,
                'total_amount'             => $total,
                'notes'                    => $this->notes ?: null,
                'purchased_at'             => now(),
            ]);

            foreach ($cleanItems as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                $product = Product::where('unit_id', $unitId)->find($item['product_id']);
                if (!$product) {
                    continue;
                }

                $product->increment('stock', (int) $item['qty']);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type'       => 'in',
                    'quantity'   => (int) $item['qty'],
                    'note'       => "Pembelian {$poNumber} dari vendor '{$vendor->name}'",
                    'user_id'    => auth()->id(),
                ]);
            }

            AuditLog::record(
                event: 'PURCHASE_ORDER_CREATED',
                identifier: $poNumber,
                description: "Admin unit mencatat pembelian {$poNumber} dari vendor '{$vendor->name}' sebesar Rp " . number_format($total, 0, ',', '.'),
                oldValues: null,
                newValues: $purchase->getAttributes()
            );
        });

        session()->flash('message', "Pembelian {$poNumber} berhasil dicatat. Transaksi keuangan & stok terkait sudah otomatis diperbarui.");
        $this->closeModal();
    }

    /**
     * Batalkan pembelian yang sudah tercatat: membalik stok (StockMovement
     * type 'out' kompensasi, BUKAN menghapus jejak movement 'in'
     * sebelumnya) dan menandai FinanceTransaction terkait sebagai
     * 'cancelled' -- bukan dihapus, supaya jejak audit tetap utuh.
     */
    public function cancelPurchase(int $id): void
    {
        $purchase = PurchaseOrder::where('unit_id', $this->currentUnitId())->findOrFail($id);

        if ($purchase->isCancelled()) {
            return;
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                $product = Product::where('unit_id', $purchase->unit_id)->find($item['product_id']);
                if (!$product) {
                    continue;
                }

                $qty = (int) $item['qty'];
                $product->decrement('stock', min($qty, $product->stock));

                StockMovement::create([
                    'product_id' => $product->id,
                    'type'       => 'out',
                    'quantity'   => $qty,
                    'note'       => "Pembatalan pembelian {$purchase->po_number}",
                    'user_id'    => auth()->id(),
                ]);
            }

            $purchase->financeTransaction?->update(['status' => 'cancelled']);
            $purchase->update(['status' => 'cancelled']);

            AuditLog::record(
                event: 'PURCHASE_ORDER_CANCELLED',
                identifier: $purchase->po_number,
                description: "Admin unit membatalkan pembelian {$purchase->po_number}; stok & transaksi keuangan terkait ikut disesuaikan.",
                oldValues: ['status' => 'completed'],
                newValues: ['status' => 'cancelled']
            );
        });

        $this->closeDetailModal();
        session()->flash('message', 'Pembelian berhasil dibatalkan.');
    }

    public function render()
    {
        $unitId = $this->currentUnitId();

        $purchases = PurchaseOrder::query()
            ->where('unit_id', $unitId)
            ->with('vendor')
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('po_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10);

        $baseQuery = PurchaseOrder::where('unit_id', $unitId)->where('status', 'completed');

        return view('livewire.unit.purchasing.index', [
            'purchases'      => $purchases,
            'vendors'        => $this->vendorOptions(),
            'products'       => $this->productOptions(),
            'totalThisMonth' => (clone $baseQuery)->whereMonth('purchased_at', now()->month)->whereYear('purchased_at', now()->year)->sum('total_amount'),
            'totalCount'     => (clone $baseQuery)->count(),
            'vendorCount'    => (clone $baseQuery)->distinct('vendor_id')->count('vendor_id'),
        ]);
    }
}
