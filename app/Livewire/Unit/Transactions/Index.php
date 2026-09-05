<?php

namespace App\Livewire\Unit\Transactions;

use App\Livewire\Master\Transactions\Index as MasterTransactionsIndex;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Monitoring Transaksi".
 *
 * Pola yang dipakai sama seperti Unit\Documents\Generate: reuse semua
 * property, validasi, dan proses dari class induk (Master\Transactions\Index)
 * apa adanya — supaya logic pencatatan transaksi tetap satu sumber
 * kebenaran. Yang diubah HANYA scoping-nya ke unit milik user login.
 *
 * KUNCI dari scoping ini adalah property publik $unitFilter & $form_unit_id
 * yang sudah dipakai konsisten oleh render()/exportData()/getTransactionsQuery()
 * di class induk. Dengan MENGUNCI dua property itu ke unit_id user login
 * (dan mencegahnya diubah balik), seluruh query, KPI, dan export Excel di
 * class induk OTOMATIS ikut ter-scope tanpa perlu menulis ulang query-nya.
 *
 * Method yang query berdasarkan ID (editTransaction, openDetail, save saat
 * edit, bulk actions) TETAP di-override secara eksplisit sebagai lapisan
 * keamanan kedua (defense in depth) — supaya walau $editingTransactionId
 * atau $selectedRows dimanipulasi lewat request, tidak bisa menyentuh
 * transaksi milik unit lain (IDOR).
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterTransactionsIndex
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

    /**
     * Cegah filter unit di tabel diubah lewat manipulasi request
     * (mis. wire:model di devtools). Dipanggil Livewire setelah properti
     * ter-update, jadi kita paksa balik ke unit sendiri di sini.
     */
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

    public function editTransaction(int $id): void
    {
        // Guard IDOR: pastikan transaksi ini memang milik unit sendiri
        // sebelum logic asli (yang tidak scoped) dari induk dijalankan.
        FinanceTransaction::where('unit_id', $this->currentUnitId())->findOrFail($id);

        parent::editTransaction($id);
    }

    /**
     * BUGFIX: induk (openCreateModal -> resetCreateForm) mereset form_unit_id
     * balik ke null setiap kali modal "Tambah Transaksi" dibuka. Sebelumnya
     * ini "ketutupan" karena dropdown masih menampilkan placeholder
     * "-- Pilih Unit Usaha --" (bareng semua unit lain yang bocor). Setelah
     * placeholder itu disembunyikan di blade (karena $units cuma 1 unit),
     * form_unit_id yang null tidak match opsi mana pun -> dropdown Unit
     * Usaha tampil KOSONG, dan Kategori Transaksi ikut kosong karena
     * daftar kategori di render() bergantung ke form_unit_id.
     *
     * Fix: kunci ulang form_unit_id SEGERA setelah modal dibuka (bukan cuma
     * di mount()/saveTransaction()), supaya form langsung terisi unit
     * sendiri dan daftar kategori langsung ikut muncul.
     */
    public function openCreateModal(): void
    {
        parent::openCreateModal();
        $this->lockUnitScope();
    }

    public function openDetail(int $id): void
    {
        FinanceTransaction::where('unit_id', $this->currentUnitId())->findOrFail($id);

        parent::openDetail($id);
    }

    public function saveTransaction(): void
    {
        $this->lockUnitScope();

        if ($this->isEditing && $this->editingTransactionId) {
            FinanceTransaction::where('unit_id', $this->currentUnitId())
                ->findOrFail($this->editingTransactionId);
        }

        parent::saveTransaction();
    }

    public function bulkDelete(): void
    {
        // Buang ID yang bukan milik unit sendiri sebelum diteruskan ke induk.
        $this->selectedRows = FinanceTransaction::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkDelete();
    }

    public function bulkUpdateStatus(string $status): void
    {
        $this->selectedRows = FinanceTransaction::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkUpdateStatus($status);
    }

    // =========================================================================
    // GUARD KATEGORI TRANSAKSI
    // =========================================================================
    // Admin Unit HANYA boleh membuat kategori khusus (scope 'specific')
    // untuk unit-nya sendiri -- TIDAK bisa memilih "Semua Unit" (scope
    // 'all') atau unit lain manapun, itu wewenang Master Admin saja
    // (lihat blade: pilihan cakupan & checklist unit disembunyikan untuk
    // role unit-admin lewat cek $units->count() === 1).
    //
    // Admin Unit juga hanya boleh mengedit/menghapus kategori yang
    // BENAR-BENAR miliknya sendiri (scope 'specific' & satu-satunya unit
    // yang terhubung adalah unit ini) -- bukan kategori "Semua Unit" atau
    // kategori 'specific' yang dibagi Master Admin ke beberapa unit
    // sekaligus (termasuk unit ini). Dua kasus terakhir itu tetap bisa
    // DIPAKAI (muncul di dropdown form transaksi via forUnit()), hanya
    // tidak bisa diubah/dihapus dari sisi Unit Admin.

    public function openCategoryModal(): void
    {
        parent::openCategoryModal();
        $this->lockCategoryScope();
    }

    private function lockCategoryScope(): void
    {
        $this->category_scope = 'specific';
        $this->category_unit_ids = [$this->currentUnitId()];
    }

    /**
     * Kategori dianggap "milik" unit ini kalau scope-nya 'specific' DAN
     * unit yang terhubung ke kategori itu HANYA unit ini (tidak dibagi ke
     * unit lain oleh Master Admin).
     */
    private function guardOwnCategory(int $id): void
    {
        $unitId = $this->currentUnitId();

        FinanceCategory::where('id', $id)
            ->where('scope', 'specific')
            ->whereHas('units', fn ($q) => $q->where('units.id', $unitId))
            ->whereDoesntHave('units', fn ($q) => $q->where('units.id', '!=', $unitId))
            ->findOrFail($id);
    }

    public function saveCategory(): void
    {
        $this->lockCategoryScope();

        if ($this->isEditingCategory && $this->editingCategoryId) {
            $this->guardOwnCategory($this->editingCategoryId);
        }

        parent::saveCategory();
    }

    public function editCategory(int $id): void
    {
        $this->guardOwnCategory($id);
        parent::editCategory($id);
    }

    public function deleteCategory(int $id): void
    {
        $this->guardOwnCategory($id);
        parent::deleteCategory($id);
    }

    /**
     * TODO (belum digarap di iterasi skeleton ini): TransactionsImport
     * (dipakai importExcel() bawaan induk) membaca unit_id LANGSUNG dari
     * kolom file Excel yang diupload user, bukan dari $this->form_unit_id.
     * Ini berarti admin unit yang mengimpor file secara teknis masih bisa
     * mencantumkan unit_id unit lain di dalam filenya sendiri.
     * Sebelum menu Import dibuka untuk role unit-admin, TransactionsImport
     * perlu diberi tahu unit_id yang mengunci (constructor/property),
     * bukan diasumsikan aman di sini.
     */

    // Query KPI, getTransactionsQuery(), dan exportData() di class induk
    // semuanya sudah memakai $this->unitFilter yang kita kunci di atas, jadi
    // otomatis ikut ter-scope ke unit sendiri. TIDAK PERLU ditulis ulang.

    /**
     * render() DIPANGGIL (bukan diskip) hanya untuk menimpa satu hal: data
     * dropdown 'units' yang dikirim induk ke blade. Induk mengirim
     * Unit::orderBy('name')->get() -- SEMUA unit usaha -- untuk mengisi
     * <select> filter "Unit Usaha" di tabel dan <select> "Unit Usaha" di
     * form tambah/edit transaksi. Value-nya memang sudah dikunci balik ke
     * unit sendiri lewat updatedUnitFilter()/updatedFormUnitId() di atas,
     * tapi opsi/nama unit LAIN tetap tampak di daftar dropdown-nya -- itu
     * kebocoran tampilan (admin unit bisa melihat nama unit usaha lain),
     * meski tidak bisa benar-benar memilihnya untuk disimpan.
     *
     * Query lain (transaksi, KPI, kategori) TIDAK disentuh di sini --
     * cukup panggil parent::render() lalu timpa key 'units' di View-nya
     * dengan koleksi berisi unit sendiri saja.
     */
    public function render()
    {
        return parent::render()->with('units', Unit::where('id', $this->currentUnitId())->get());
    }
}