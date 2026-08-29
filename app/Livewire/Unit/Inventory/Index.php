<?php

namespace App\Livewire\Unit\Inventory;

use App\Livewire\Master\Inventory\Index as MasterInventoryIndex;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Inventaris / Stok Produk".
 *
 * Sama seperti Unit\Transactions\Index: reuse penuh logic dari
 * Master\Inventory\Index, dikunci lewat property $unitFilter &
 * $form_unit_id yang sudah dipakai konsisten di getFilteredProductsQuery(),
 * render(), updatedSelectAll(), dan exportProducts() milik induk.
 *
 * Kategori produk (Category model) juga punya unit_id, jadi CRUD kategori
 * ikut dikunci lewat $category_unit_id.
 *
 * Method berbasis ID (editProduct, deleteProduct, openStockModal,
 * editCategory, deleteCategory) di-guard eksplisit sebagai lapis kedua
 * terhadap IDOR.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterInventoryIndex
{
    use ScopedToUnit;

    public function mount(): void
    {
        parent::mount();
        $this->lockUnitScope();
    }

    private function lockUnitScope(): void
    {
        $this->unitFilter = $this->currentUnitId();
        $this->form_unit_id = $this->currentUnitId();
    }

    public function updatedUnitFilter(): void
    {
        $this->unitFilter = $this->currentUnitId();
    }

    public function updatedFormUnitId(): void
    {
        $this->form_unit_id = $this->currentUnitId();
        parent::updatedFormUnitId();
    }

    public function resetFilters(): void
    {
        parent::resetFilters();
        $this->unitFilter = $this->currentUnitId();
    }

    // =========================================================================
    // GUARD PRODUK
    // =========================================================================

    /**
     * BUGFIX (sama seperti Unit\Transactions\Index::openCreateModal()):
     * induk (openCreateModal -> resetProductForm) mereset form_unit_id
     * balik ke null tiap kali modal "Tambah Produk" dibuka. Kunci ulang di
     * sini supaya dropdown Unit Usaha langsung terisi unit sendiri (bukan
     * kosong) dan formCategories di render() (yang bergantung ke
     * form_unit_id) langsung ikut muncul tanpa perlu user pilih manual.
     */
    public function openCreateModal(): void
    {
        parent::openCreateModal();
        $this->lockUnitScope();
    }

    public function editProduct(int $id): void
    {
        Product::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::editProduct($id);
    }

    public function deleteProduct(int $id): void
    {
        Product::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::deleteProduct($id);
    }

    public function openStockModal(int $id): void
    {
        Product::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::openStockModal($id);
    }

    public function deleteSelected(): void
    {
        $this->selectedRows = Product::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::deleteSelected();
    }

    public function exportSelected()
    {
        $this->selectedRows = Product::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return parent::exportSelected();
    }

    // =========================================================================
    // GUARD KATEGORI PRODUK
    // =========================================================================

    public function openCategoryModal(): void
    {
        parent::openCategoryModal();
        $this->category_unit_id = $this->currentUnitId();
    }

    public function saveCategory(): void
    {
        // Kunci kategori baru/diedit selalu ke unit sendiri. CATATAN
        // (koreksi dari komentar sebelumnya): blade TIDAK menyembunyikan
        // dropdown Unit Usaha di form ini untuk role unit-admin -- dropdown
        // itu sekarang dipersempit isinya lewat render() di bawah (cuma
        // berisi unit sendiri), tapi baris ini tetap wajib ada sebagai
        // lapis kedua terhadap manipulasi request langsung ke property
        // category_unit_id (bukan cuma lewat select di UI).
        $this->category_unit_id = $this->currentUnitId();

        if ($this->isEditingCategory && $this->editingCategoryId) {
            Category::where('unit_id', $this->currentUnitId())->findOrFail($this->editingCategoryId);
        }

        parent::saveCategory();
    }

    public function editCategory(int $id): void
    {
        Category::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::editCategory($id);
    }

    public function deleteCategory(int $id): void
    {
        Category::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::deleteCategory($id);
    }

    /**
     * TODO (belum digarap di iterasi skeleton ini): sama seperti Transactions,
     * ProductsImport membaca unit_id dari kolom file Excel yang diupload,
     * bukan dari $this->form_unit_id. Perlu penyesuaian di ProductsImport
     * sebelum menu Import Stok dibuka untuk role unit-admin.
     */

    // updatedSelectAll(), generateProductCode() SENGAJA tidak di-override:
    // keduanya sudah memakai $this->unitFilter / $this->form_unit_id yang
    // dikunci di atas, jadi otomatis ikut ter-scope ke unit sendiri.

    /**
     * render() DIPANGGIL hanya untuk menimpa dua key dropdown yang dikirim
     * induk apa adanya ke semua unit usaha / semua kategori lintas unit:
     * - 'units'      -> dipakai di filter tabel, form produk, DAN dropdown
     *                   Unit Usaha pada modal kategori (lihat blade baris
     *                   ~62, ~315, ~508).
     * - 'categories' -> dipakai di dropdown filter "Kategori" pada tabel
     *                   (blade baris ~82). Ini BEDA dari 'formCategories'
     *                   (dipakai di form tambah/edit produk) yang sudah
     *                   otomatis ter-scope oleh induk karena bergantung ke
     *                   $this->form_unit_id yang sudah kita kunci.
     *
     * Query produk, KPI, dan formCategories TIDAK disentuh -- cukup panggil
     * parent::render() lalu timpa dua key itu di View-nya.
     */
    public function render()
    {
        $unitId = $this->currentUnitId();

        return parent::render()->with([
            'units' => Unit::where('id', $unitId)->get(),
            'categories' => Category::where('unit_id', $unitId)->orderBy('name')->get(),
        ]);
    }
}