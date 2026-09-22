<?php

namespace App\Livewire\Master\Purchasing;

use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Unit;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Pembelian Lintas-Unit" -- pasangan Master dari App\Livewire\Unit\Purchasing\Index.
 *
 * SEBELUMNYA modul ini READ-ONLY (cuma memantau & merekap belanja seluruh
 * unit). SEKARANG ditambah form Catat Pembelian & aksi Batalkan sendiri di
 * sisi Master -- persis seperti yang sudah dijelaskan lebih dulu di komentar
 * route 'master.purchasing.index' (lihat routes/web.php). Alur, validasi,
 * dan efek sampingnya (StockMovement + FinanceTransaction) ditulis SEJAJAR
 * (mirror 1:1) dengan Unit\Purchasing\Index supaya kedua modul konsisten dan
 * mudah dirawat berdampingan; perbedaan utamanya cuma dua:
 *
 *   1. TIDAK memakai trait ScopedToUnit -- trait itu mengunci ke unit yang
 *      terikat pada ROUTE (request()->route('unit')), yang memang tidak ada
 *      di halaman ini ('/master/pembelian', bukan '/unit/{unit:slug}/...').
 *      Sebagai gantinya form Tambah punya field 'unit_id' sendiri (dropdown
 *      Unit Usaha) -- pola yang sama persis dipakai Master\ServiceOrder\Index
 *      & Master\RecurringTransaction\Index untuk kasus serupa (data yang
 *      dibuat Master Admin tapi harus terikat ke SATU unit tertentu).
 *   2. Query tabel & pembatalan TIDAK dikunci ke satu unit (tetap lintas
 *      unit untuk keperluan pemantauan/rekap), tapi stok & transaksi
 *      keuangan yang DIBUAT tetap terkunci ke unit yang DIPILIH di form.
 *
 * Tidak ada middleware 'unit.category:...' (sama seperti sebelumnya):
 * Pembelian berlaku untuk SEMUA kategori unit, bukan cuma 'jasa'.
 */
#[Layout('components.layouts.app')]
#[Title('Pembelian Lintas-Unit')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $unitFilter = '';
    public string $vendorFilter = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $showDetailModal = false;
    public ?PurchaseOrder $selectedPurchase = null;

    // ================= Form Inputs (Catat Pembelian) =================
    public string $unit_id = '';
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
    public function updatingUnitFilter(): void { $this->resetPage(); }
    public function updatingVendorFilter(): void { $this->resetPage(); }
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
     * Saat Unit Usaha di form diganti, baris item yang sudah memilih Produk
     * dari unit SEBELUMNYA jadi tidak relevan lagi (dropdown Produk ter-scope
     * per unit) -- reset ke satu baris kosong supaya tidak ada product_id
     * "nyasar" dari unit yang berbeda ikut terkirim.
     */
    public function updatedUnitId(): void
    {
        $this->resetItems();
    }

    /**
     * Ketika baris item memilih Produk dari dropdown, isi otomatis nama &
     * harga beli dari Produk tersebut supaya user tidak perlu ketik ulang
     * (harga tetap bisa diedit manual kalau harga beli kali ini berbeda).
     * Dropdown Produk sendiri sudah ter-scope ke $this->unit_id (lihat
     * productOptions()), jadi pencarian di sini ikut memakai unit yang sama.
     */
    public function updatedItems($value, $key): void
    {
        if (!str_ends_with($key, '.product_id') || !$this->unit_id) {
            return;
        }

        $index = explode('.', $key)[0];
        $productId = $this->items[$index]['product_id'] ?? null;

        if (!$productId) {
            return;
        }

        $product = Product::where('unit_id', $this->unit_id)->find($productId);

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
            'unit_id'                   => 'required|exists:units,id',
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
            'unit_id'            => 'unit usaha',
            'items.*.name'       => 'nama item',
            'items.*.qty'        => 'jumlah',
            'items.*.unit_price' => 'harga satuan',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset(['unit_id', 'vendor_id', 'notes']);
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
        $this->selectedPurchase = PurchaseOrder::with(['unit', 'vendor', 'user', 'financeTransaction'])
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
     * SEKALIGUS supplier), sama seperti di sisi Unit. Tidak dibatasi ke
     * unit manapun karena buku alamat Vendor & Supplier memang lintas unit.
     */
    private function vendorOptions()
    {
        return Vendor::orderBy('name')->get();
    }

    /**
     * Daftar Produk untuk dropdown baris item, ter-scope ke Unit Usaha yang
     * SEDANG DIPILIH di form (bukan unit Master Admin sendiri -- Master
     * Admin memang tidak terikat unit manapun). Kosong selama belum ada
     * Unit Usaha yang dipilih, supaya user tidak salah pilih Produk dari
     * unit yang keliru sebelum menentukan unit tujuan pembelian.
     */
    private function productOptions()
    {
        if (!$this->unit_id) {
            return collect();
        }

        return Product::where('unit_id', $this->unit_id)->orderBy('name')->get();
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

        $unitId = (int) $this->unit_id;
        $unit = Unit::findOrFail($unitId);
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

        DB::transaction(function () use ($unitId, $unit, $vendor, $cleanItems, $total, $poNumber) {
            // Kategori keuangan tidak lagi punya kolom unit_id -- pakai helper
            // firstOrCreateForUnit() (kategori "Pembelian" yang sudah berlaku
            // untuk unit ini, atau buat baru berscope 'specific'), sama
            // persis seperti di sisi Unit.
            $category = FinanceCategory::firstOrCreateForUnit('Pembelian', 'expense', $unitId);

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
                description: "Admin master mencatat pembelian {$poNumber} dari vendor '{$vendor->name}' untuk unit '{$unit->name}' sebesar Rp " . number_format($total, 0, ',', '.'),
                oldValues: null,
                newValues: $purchase->getAttributes()
            );
        });

        session()->flash('message', "Pembelian {$poNumber} berhasil dicatat. Transaksi keuangan & stok unit terkait sudah otomatis diperbarui.");
        $this->closeModal();
    }

    /**
     * Batalkan pembelian yang sudah tercatat: membalik stok (StockMovement
     * type 'out' kompensasi, BUKAN menghapus jejak movement 'in'
     * sebelumnya) dan menandai FinanceTransaction terkait sebagai
     * 'cancelled' -- bukan dihapus, supaya jejak audit tetap utuh. Identik
     * dengan Unit\Purchasing\Index::cancelPurchase(), hanya saja di sini
     * TIDAK dikunci ke satu unit (Master Admin boleh membatalkan pembelian
     * unit mana pun).
     */
    public function cancelPurchase(int $id): void
    {
        $purchase = PurchaseOrder::findOrFail($id);

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
                description: "Admin master membatalkan pembelian {$purchase->po_number} (Unit: {$purchase->unit?->name}); stok & transaksi keuangan terkait ikut disesuaikan.",
                oldValues: ['status' => 'completed'],
                newValues: ['status' => 'cancelled']
            );
        });

        $this->closeDetailModal();
        session()->flash('message', 'Pembelian berhasil dibatalkan.');
    }

    private function getFilteredQuery()
    {
        return PurchaseOrder::query()
            ->with(['unit', 'vendor'])
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('po_number', 'like', '%' . $this->search . '%')
                        ->orWhereHas('vendor', fn ($v) => $v->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhereHas('unit', fn ($u) => $u->where('name', 'like', '%' . $this->search . '%'));
                });
            })
            ->when($this->unitFilter, fn ($query) => $query->where('unit_id', $this->unitFilter))
            ->when($this->vendorFilter, fn ($query) => $query->where('vendor_id', $this->vendorFilter))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter));
    }

    /**
     * Rekap total belanja per vendor (status 'completed' saja), dipakai
     * untuk kartu "Top Vendor" -- landasan data untuk negosiasi kontrak
     * terpusat yang disebut di deskripsi fitur.
     */
    private function vendorRecap()
    {
        return PurchaseOrder::query()
            ->where('status', 'completed')
            ->select('vendor_id', DB::raw('SUM(total_amount) as total_belanja'), DB::raw('COUNT(*) as jumlah_po'))
            ->groupBy('vendor_id')
            ->orderByDesc('total_belanja')
            ->with('vendor')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $purchases = $this->getFilteredQuery()
            ->latest('purchased_at')
            ->latest('id')
            ->paginate(10);

        $completedQuery = PurchaseOrder::where('status', 'completed');

        return view('livewire.master.purchasing.index', [
            'purchases'      => $purchases,
            'units'          => Unit::orderBy('name')->get(),
            'vendors'        => $this->vendorOptions(),
            'products'       => $this->productOptions(),
            'vendorRecap'    => $this->vendorRecap(),
            'totalBelanja'   => (clone $completedQuery)->sum('total_amount'),
            'totalPo'        => (clone $completedQuery)->count(),
            'totalThisMonth' => (clone $completedQuery)->whereMonth('purchased_at', now()->month)->whereYear('purchased_at', now()->year)->sum('total_amount'),
        ]);
    }
}