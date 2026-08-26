<?php

namespace App\Livewire\Master\Inventory;

use App\Exports\ProductTemplateExport;
use App\Imports\ProductsImport;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Models\AuditLog;
use App\Notifications\SystemNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductExport;

#[Layout('components.layouts.app')]
#[Title('Inventaris Produk')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    // Filter & State Variables
    public string $search = '';
    public ?int $unitFilter = null;
    public string $stockFilter = '';
    public ?int $categoryFilter = null;
    public int $perPage = 15;

    public bool $selectAll = false;
    public array $selectedRows = [];

    // State Modal & Form Produk
    public bool $showCreateModal = false;
    public bool $isEditing = false;
    public ?int $editingProductId = null;

    // Field Form Produk
    public string $form_name = '';
    public string $form_code = '';
    public ?int $form_unit_id = null;
    public ?int $form_category_id = null;
    public ?int $form_purchase_price = null;
    public ?int $form_selling_price = null;
    public int $form_stock = 0;
    public int $form_min_stock = 5;
    public string $form_unit_type = 'pcs';
    public string $form_description = '';
    public $form_image;
    public ?string $existingImage = null;

    // State Modal Kelola Kategori
    public bool $showCategoryModal = false;
    public bool $isEditingCategory = false;
    public ?int $editingCategoryId = null;
    public string $category_name = '';
    public ?int $category_unit_id = null;

    // State Modal Restock / Penyesuaian Stok
    public bool $showStockModal = false;
    public ?Product $selectedProduct = null;
    public string $stock_type = 'add'; // 'add', 'subtract', atau 'set'
    public ?int $stock_quantity = null;
    public ?string $stock_note = null;

    // State Modal Import Produk
    public bool $showImportModal = false;
    public $importFile;

    // =========================================================================
    // STATE MODAL PERINGATAN / ERROR IMPORT (SESUAI DENGAN BLADE)
    // =========================================================================
    public bool $showErrorModal = false;
    public array $importErrors = [];

    // Lifecycle Hooks Filter
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingUnitFilter(): void { $this->resetPage(); }
    public function updatingStockFilter(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function mount(): void
    {
        // Sinkronisasi notifikasi stok menipis/habis saat halaman dibuka
        $this->syncAllStockNotifications();
    }

    // Reset pilihan kategori saat Unit Usaha pada form produk diubah
    public function updatedFormUnitId(): void
    {
        $this->form_category_id = null;
    }

    // =========================================================================
    // NOTIFIKASI STOK (LOW STOCK / OUT OF STOCK)
    // =========================================================================

    /**
     * Jalankan pengecekan notifikasi stok untuk SEMUA produk.
     * Dipanggil saat halaman dimuat & setelah proses import,
     * karena kedua proses ini bisa mengubah banyak stok sekaligus.
     */
    private function syncAllStockNotifications(): void
    {
        Product::all()->each(function (Product $product) {
            $this->syncStockNotification($product);
        });
    }

    /**
     * Sinkronisasi status notifikasi untuk SATU produk:
     * - Kirim notifikasi baru jika stok menipis/habis dan belum ada notifikasi aktif.
     * - Tandai notifikasi lama sebagai dibaca jika stok sudah kembali aman.
     */
    private function syncStockNotification(Product $product): void
    {
        $product->refresh();

        if ($product->stock <= 0) {
            $this->fireStockAlert(
                $product,
                'out_of_stock',
                'Stok Habis',
                "Stok produk '{$product->name}' telah habis (0 {$product->unit_type}). Segera lakukan restock."
            );
            $this->clearStockAlert($product, 'low_stock');
        } elseif ($product->stock <= $product->min_stock) {
            $this->fireStockAlert(
                $product,
                'low_stock',
                'Stok Menipis',
                "Stok produk '{$product->name}' tersisa {$product->stock} {$product->unit_type} (batas minimum: {$product->min_stock})."
            );
            $this->clearStockAlert($product, 'out_of_stock');
        } else {
            $this->clearStockAlert($product, 'low_stock');
            $this->clearStockAlert($product, 'out_of_stock');
        }
    }

    private function fireStockAlert(Product $product, string $type, string $title, string $message): void
    {
        foreach (User::all() as $user) {
            $hasPending = $user->unreadNotifications()
                ->where('data->product_id', $product->id)
                ->where('data->inventory_alert_type', $type)
                ->exists();

            if (!$hasPending) {
                $user->notify(new SystemNotification(
                    title: $title,
                    message: $message,
                    badge: $type === 'out_of_stock' ? 'Stok Habis' : 'Stok Menipis',
                    actionable: false,
                    url: url()->current(),
                    extraData: [
                        'product_id'            => $product->id,
                        'inventory_alert_type'  => $type,
                    ]
                ));
            }
        }
    }

    private function clearStockAlert(Product $product, string $type): void
    {
        foreach (User::all() as $user) {
            $user->unreadNotifications()
                ->where('data->product_id', $product->id)
                ->where('data->inventory_alert_type', $type)
                ->update(['read_at' => now()]);
        }
    }

    // =========================================================================
    // IMPORT & EXPORT PRODUK
    // =========================================================================

    public function openImportModal(): void
    {
        $this->resetErrorBag();
        $this->reset('importFile');
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset('importFile');
        $this->resetErrorBag('importFile');
    }

    public function closeImportErrorModal(): void
    {
        $this->showErrorModal = false;
        $this->importErrors = [];
    }

    public function downloadTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'template_import_produk.xlsx');
    }

    public function importProducts(): void
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ], [
            'importFile.required' => 'File berkas wajib diunggah.',
            'importFile.mimes'    => 'Format file harus berupa .xlsx, .xls, atau .csv',
            'importFile.max'      => 'Ukuran berkas maksimal 5 MB.',
        ]);

        $import = new ProductsImport();

        // Eksekusi Import
        Excel::import($import, $this->importFile);

        // Tangkap Peringatan / Kesalahan Validasi per Baris
        if ($import->failures()->isNotEmpty()) {
            $this->importErrors = [];
            $customAttributes = $import->customValidationAttributes();

            foreach ($import->failures() as $failure) {
                $attr = $failure->attribute();
                $columnLabel = $customAttributes[$attr] ?? ucwords(str_replace('_', ' ', $attr));

                // Struktur array ini langsung dicetak oleh foreach di Blade Anda
                $this->importErrors[] = [
                    'row'      => $failure->row(),                          // Baris ke-N
                    'column'   => $columnLabel,                            // Nama Kolom
                    'value'    => $failure->values()[$attr] ?? '-',        // Input pengguna
                    'messages' => implode(', ', $failure->errors()),       // Keterangan Masalah
                ];
            }

            AuditLog::record(
                event: 'PRODUCT_IMPORT_FAILED',
                identifier: $this->importFile->getClientOriginalName(),
                description: 'Admin gagal melakukan impor produk. Terdapat kesalahan validasi pada berkas Excel.'
            );

            $this->closeImportModal();
            $this->showErrorModal = true; // Menampilkan Modal Error Blade
            return;
        }

        // Catat ke Audit Log
        AuditLog::record(
            event: 'PRODUCT_IMPORTED',
            identifier: $this->importFile->getClientOriginalName(),
            description: 'Admin melakukan impor produk melalui Excel',
            newValues: [
                'nama_file'   => $this->importFile->getClientOriginalName(),
                'ukuran_file' => round($this->importFile->getSize() / 1024, 2) . ' KB',
                'format'      => $this->importFile->getClientOriginalExtension(),
                'waktu_impor' => now()->translatedFormat('d F Y H:i:s'),
            ]
        );

        // Sinkronisasi notifikasi stok karena import bisa mengubah banyak stok sekaligus
        $this->syncAllStockNotifications();

        $this->closeImportModal();
        session()->flash('success', 'Data produk berhasil diimport.');
    }

    public function exportProducts()
    {
        $filters = [
            'search'         => $this->search,
            'unitFilter'     => $this->unitFilter,
            'categoryFilter' => $this->categoryFilter,
            'stockFilter'    => $this->stockFilter,
        ];

        $fileName = 'Data_Produk_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            event: 'PRODUCT_EXPORTED',
            identifier: $fileName,
            description: 'Admin mengekspor data produk ke Excel' .
                ($this->unitFilter ? " (Unit ID: {$this->unitFilter})" : '') .
                ($this->stockFilter ? " status stok: {$this->stockFilter}" : ''),
        );

        return Excel::download(new ProductExport($filters), $fileName);
    }

    // =========================================================================
    // MANAGEMENT KATEGORI (LOGIC MODAL)
    // =========================================================================

    public function openCategoryModal(): void
    {
        $this->resetCategoryForm();
        if ($this->form_unit_id) {
            $this->category_unit_id = $this->form_unit_id;
        }
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function resetCategoryForm(): void
    {
        $this->reset(['category_name', 'category_unit_id', 'editingCategoryId', 'isEditingCategory']);
        $this->resetErrorBag(['category_name', 'category_unit_id']);
    }

    public function saveCategory(): void
    {
        $this->validate([
            'category_name'    => 'required|string|max:255',
            'category_unit_id' => 'required|exists:units,id',
        ], [
            'category_name.required'    => 'Nama kategori wajib diisi.',
            'category_unit_id.required' => 'Unit Usaha wajib dipilih.',
        ]);

        $isEdit = $this->isEditingCategory;

        // 1. Ambil oldValues SEBELUM data di-update
        $oldCategory = $isEdit ? Category::find($this->editingCategoryId) : null;
        $oldValues = $oldCategory ? $oldCategory->getAttributes() : null;

        // 2. Eksekusi updateOrCreate cukup SATU kali
        $category = Category::updateOrCreate(
            ['id' => $this->editingCategoryId],
            [
                'name'    => $this->category_name,
                'unit_id' => $this->category_unit_id,
            ]
        );

        if ($this->showCreateModal && $this->form_unit_id == $category->unit_id) {
            $this->form_category_id = $category->id;
        }

        // 3. Catat Audit Log
        AuditLog::record(
            event: $isEdit ? 'CATEGORY_UPDATED' : 'CATEGORY_CREATED',
            identifier: (string) $category->id,
            description: $isEdit 
                ? "Admin memperbarui kategori produk: {$category->name}" 
                : "Admin menambahkan kategori produk baru: {$category->name}",
            oldValues: $oldValues,
            newValues: $category->getAttributes()
        );

        $this->resetCategoryForm();
        session()->flash('category_success', $isEdit ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
    }

    public function editCategory(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingCategoryId = $category->id;
        $this->category_name = $category->name;
        $this->category_unit_id = $category->unit_id;
        $this->isEditingCategory = true;
    }

    public function deleteCategory(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->products()->count() > 0) {
            session()->flash('category_error', 'Kategori tidak dapat dihapus karena masih digunakan oleh produk.');
            return;
        }

        // 1. Ambil data SEBELUM dihapus
        $categoryName = $category->name;
        $oldValues = $category->getAttributes();

        // 2. Hapus data (cukup 1 kali)
        $category->delete();

        if ($this->editingCategoryId === $id) {
            $this->resetCategoryForm();
        }

        // 3. Catat Audit Log
        AuditLog::record(
            event: 'CATEGORY_DELETED',
            identifier: (string) $id,
            description: "Admin menghapus kategori produk: {$categoryName}",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('category_success', 'Kategori berhasil dihapus.');
    }

    // =========================================================================
    // MANAGEMENT STOK (RESTOCK / ADJUSTMENT)
    // =========================================================================

    public function openStockModal(int $id): void
    {
        $this->selectedProduct = Product::findOrFail($id);
        $this->stock_type = 'add';
        $this->stock_quantity = null;
        $this->stock_note = null;
        $this->resetValidation();
        $this->showStockModal = true;
    }

    public function closeStockModal(): void
    {
        $this->showStockModal = false;
        $this->selectedProduct = null;
        $this->resetValidation();
    }

    public function saveStock(): void
    {
        $this->validate([
            'stock_type'     => 'required|in:add,subtract,set',
            'stock_quantity' => 'required|integer|min:1',
            'stock_note'     => 'nullable|string|max:255',
        ], [
            'stock_type.required'     => 'Jenis penyesuaian wajib dipilih.',
            'stock_quantity.required' => 'Jumlah stok wajib diisi.',
            'stock_quantity.min'      => 'Jumlah stok minimal 1 unit.',
        ]);

        if (!$this->selectedProduct) {
            return;
        }

        $currentStock = $this->selectedProduct->stock;
        $newStock = $currentStock;

        if ($this->stock_type === 'add') {
            $newStock = $currentStock + $this->stock_quantity;
        } elseif ($this->stock_type === 'subtract') {
            if ($this->stock_quantity > $currentStock) {
                $this->addError('stock_quantity', 'Jumlah pengurangan melebihi stok yang tersedia saat ini.');
                return;
            }
            $newStock = $currentStock - $this->stock_quantity;
        } elseif ($this->stock_type === 'set') {
            $newStock = $this->stock_quantity;
        }

        AuditLog::record(
            event: 'STOCK_ADJUSTMENT',
            identifier: $this->selectedProduct->code ?? "ID: {$this->selectedProduct->id}",
            description: "Admin mengubah stok '{$this->selectedProduct->name}' dari {$currentStock} menjadi {$newStock}" . ($this->stock_note ? " ({$this->stock_note})" : ""),
            oldValues: ['stock' => $currentStock],
            newValues: ['stock' => $newStock]
        );

        $this->selectedProduct->update([
            'stock' => $newStock,
        ]);

        // Sinkronisasi notifikasi stok setelah penyesuaian
        $this->syncStockNotification($this->selectedProduct);

        $productName = $this->selectedProduct->name;
        $this->closeStockModal();
        session()->flash('success', "Stok produk '{$productName}' berhasil diperbarui.");
    }

    // =========================================================================
    // MANAGEMENT PRODUK (CRUD & ACTIONS)
    // =========================================================================

    public function resetFilters(): void
    {
        $this->reset(['search', 'unitFilter', 'stockFilter', 'categoryFilter']);
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetProductForm();
        $this->isEditing = false;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetProductForm();
    }

    public function editProduct(int $id): void
    {
        $product = Product::findOrFail($id);

        $this->editingProductId    = $product->id;
        $this->form_name           = $product->name;
        $this->form_code           = $product->code ?? '';
        $this->form_unit_id        = $product->unit_id;
        $this->form_category_id    = $product->category_id;
        $this->form_purchase_price = $product->purchase_price;
        $this->form_selling_price  = $product->selling_price;
        $this->form_stock          = $product->stock;
        $this->form_min_stock      = $product->min_stock ?? 5;
        $this->form_unit_type      = $product->unit_type ?? 'pcs';
        $this->form_description    = $product->description ?? '';
        $this->existingImage       = $product->image;
        $this->form_image          = null;

        $this->isEditing = true;
        $this->showCreateModal = true;
    }

    public function generateProductCode(): void
    {
        $prefix = 'PRD-';
        do {
            $code = $prefix . strtoupper(Str::random(6));
        } while (
            Product::where('code', $code)
                ->when($this->form_unit_id, fn($q) => $q->where('unit_id', $this->form_unit_id))
                ->exists()
        );
        $this->form_code = $code;
    }

    public function saveProduct(): void
    {
        $this->validate([
            'form_name'           => 'required|string|max:255',
            'form_code'           => [
                'required', 'string', 'max:255',
                Rule::unique('products', 'code')
                    ->where(fn ($q) => $q->where('unit_id', $this->form_unit_id))
                    ->ignore($this->editingProductId),
            ],
            'form_unit_id'        => 'required|exists:units,id',
            'form_category_id'    => 'required|exists:categories,id',
            'form_purchase_price' => 'nullable|numeric|min:0',
            'form_selling_price'  => 'required|numeric|min:0',
            'form_stock'          => 'required|integer|min:0',
            'form_min_stock'      => 'nullable|integer|min:0',
            'form_unit_type'      => 'nullable|string|max:20',
            'form_description'    => 'nullable|string',
            'form_image'          => 'nullable|image|max:2048',
        ]);

        $oldProduct = $this->isEditing ? Product::find($this->editingProductId) : null;
        $oldValues = $oldProduct ? $oldProduct->getAttributes() : null;

        $imagePath = $this->existingImage;
        if ($this->form_image) {
            if ($this->isEditing && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $imagePath = $this->form_image->store('products', 'public');
        }

        $product = Product::updateOrCreate(
            ['id' => $this->editingProductId],
            [
                'name'           => $this->form_name,
                'code'           => $this->form_code,
                'unit_id'        => $this->form_unit_id,
                'category_id'    => $this->form_category_id,
                'purchase_price' => $this->form_purchase_price ?? 0,
                'selling_price'  => $this->form_selling_price,
                'stock'          => $this->form_stock,
                'min_stock'      => $this->form_min_stock,
                'unit_type'      => $this->form_unit_type ?: 'pcs',
                'description'    => $this->form_description,
                'image'          => $imagePath,
            ]
        );

        AuditLog::record(
            event: $this->isEditing ? 'PRODUCT_UPDATED' : 'PRODUCT_CREATED',
            identifier: $product->code,
            description: $this->isEditing 
                ? "Admin memperbarui produk: {$product->name}" 
                : "Admin menambahkan produk baru: {$product->name}",
            oldValues: $oldValues,
            newValues: $product->getAttributes()
        );

        // Sinkronisasi notifikasi stok (relevan jika stok/min_stock diubah lewat form)
        $this->syncStockNotification($product);

        $this->closeCreateModal();
        session()->flash('success', $this->isEditing ? 'Produk berhasil diperbarui.' : 'Produk berhasil ditambahkan.');
    }

    private function resetProductForm(): void
    {
        $this->reset([
            'isEditing', 'editingProductId', 'form_name', 'form_code',
            'form_unit_id', 'form_category_id', 'form_purchase_price',
            'form_selling_price', 'form_stock', 'form_min_stock',
            'form_unit_type', 'form_description', 'form_image', 'existingImage',
        ]);
        $this->form_min_stock = 5;
        $this->form_unit_type = 'pcs';
        $this->resetValidation();
    }

    // =========================================================================
    // BULK SELECTION & QUERY
    // =========================================================================

    private function getFilteredProductsQuery()
    {
        return Product::with(['unit', 'category'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                          ->orWhere('code', 'like', "%{$this->search}%")
                          ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->stockFilter === 'out', fn ($q) => $q->where('stock', '<=', 0))
            ->when($this->stockFilter === 'low', fn ($q) => $q->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock'))
            ->when($this->stockFilter === 'normal', fn ($q) => $q->whereColumn('stock', '>', 'min_stock'));
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedRows = $this->getFilteredProductsQuery()->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function deselectAll(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedRows)) {
            return;
        }

        $products = Product::whereIn('id', $this->selectedRows)->get();

        foreach ($products as $product) {
            AuditLog::record(
                event: 'PRODUCT_DELETED',
                identifier: $product->code ?? "ID: {$product->id}",
                description: "Admin menghapus produk (Bulk Delete): {$product->name}",
                oldValues: $product->only(['id', 'name', 'code', 'selling_price', 'stock']),
                newValues: null
            );

            // Bersihkan notifikasi stok terkait produk yang dihapus
            $this->clearStockAlert($product, 'low_stock');
            $this->clearStockAlert($product, 'out_of_stock');

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->delete();
        }

        $this->deselectAll();
        session()->flash('success', 'Produk terpilih berhasil dihapus.');
    }

    public function exportSelected()
    {
        if (empty($this->selectedRows)) {
            session()->flash('error', 'Pilih minimal satu produk terlebih dahulu untuk diekspor.');
            return;
        }

        $fileName = 'Data_Produk_Terpilih_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            event: 'PRODUCT_EXPORTED',
            identifier: $fileName,
            description: 'Admin mengekspor ' . count($this->selectedRows) . ' produk terpilih ke Excel',
        );

        return Excel::download(new ProductExport([], $this->selectedRows), $fileName);
    }

    public function deleteProduct(int $id): void
    {
        $product = Product::findOrFail($id);
        $productName = $product->name;
        $productCode = $product->code ?? "ID: {$id}";
        $oldValues   = $product->only(['id', 'name', 'code', 'purchase_price', 'selling_price', 'stock', 'unit_id', 'category_id']);

        AuditLog::record(
            event: 'PRODUCT_DELETED',
            identifier: $productCode,
            description: "Admin menghapus produk: {$productName}",
            oldValues: $oldValues,
            newValues: null
        );

        // Bersihkan notifikasi stok terkait produk yang dihapus
        $this->clearStockAlert($product, 'low_stock');
        $this->clearStockAlert($product, 'out_of_stock');

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        $this->selectedRows = array_diff($this->selectedRows, [(string) $id]);
        session()->flash('success', 'Produk berhasil dihapus.');
    }

    public function render()
    {
        $query = $this->getFilteredProductsQuery();

        $formCategories = $this->form_unit_id
            ? Category::where('unit_id', $this->form_unit_id)->orderBy('name')->get()
            : Category::orderBy('name')->get();

        return view('livewire.master.inventory.index', [
            'products'            => $query->orderBy('name')->paginate($this->perPage),
            'units'               => Unit::orderBy('name')->get(),
            'categories'          => Category::orderBy('name')->get(),
            'formCategories'      => $formCategories,
            'totalProductsCount'  => (clone $query)->count(),
            'totalStockSum'       => (clone $query)->sum('stock'),
            'lowStockCount'       => (clone $query)->whereColumn('stock', '<=', 'min_stock')->count(),
            'totalInventoryValue' => (clone $query)->reorder()->selectRaw('SUM(stock * purchase_price) as total_value')->value('total_value') ?? 0,
        ]);
    }
}