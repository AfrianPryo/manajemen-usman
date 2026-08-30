<?php

namespace App\Livewire\Unit\Asset;

use App\Livewire\Master\Asset\Index as MasterAssetIndex;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\Asset;
use App\Models\Unit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Aset Unit Usaha".
 *
 * Sebelumnya halaman ini sengaja dikosongkan karena tabel `assets` belum
 * punya kolom `unit_id` sama sekali (lihat migrasi
 * add_unit_id_to_assets_table & App\Models\Asset::unit()). Sekarang kolom
 * itu sudah ada, jadi modul ini diisi mengikuti POLA YANG SAMA seperti
 * Unit\Transactions\Index dan Unit\Inventory\Index: extends
 * Master\Asset\Index apa adanya (supaya CRUD, KPI, import/export tetap
 * satu sumber kebenaran), dikunci ke unit lewat trait ScopedToUnit, dan
 * setiap method berbasis ID diberi guard eksplisit sebagai lapis kedua
 * terhadap IDOR (defense in depth) -- persis seperti kedua modul itu.
 *
 * unit_id di tabel `assets` bersifat NULLABLE (aset "Pusat / Tanpa Unit"
 * tetap valid untuk Master Admin), tapi begitu form ini dibuka dari sisi
 * Unit Admin, unit_id SELALU dikunci ke unit sendiri lewat lockUnitScope()
 * -- Unit Admin tidak bisa membuat/mengubah aset jadi "Pusat" maupun milik
 * unit lain.
 *
 * TODO (sama seperti Products/Transactions Import, belum digarap di
 * iterasi ini): AssetsImport::model() melakukan
 * Asset::updateOrCreate(['asset_tag' => $tag], [...]) TANPA menyertakan
 * unit_id dari konteks yang sedang login -- artinya baris Excel yang
 * diimpor lewat menu ini akan tersimpan sebagai "Pusat / Tanpa Unit", dan
 * kalau tag aset di file kebetulan sama dengan aset unit LAIN yang sudah
 * ada, baris itu akan meng-update aset unit lain tersebut (IDOR lewat
 * import). Sebelum menu Import Excel dibuka untuk role unit-admin,
 * AssetsImport perlu diberi tahu unit_id yang mengunci (lewat constructor,
 * mirip pola yang disarankan di TODO Unit\Transactions\Index) dan
 * query pencarian tag existing-nya perlu ikut di-scope ke unit itu.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterAssetIndex
{
    use ScopedToUnit;

    public function mount()
    {
        parent::mount();
        $this->lockUnitScope();
    }

    private function lockUnitScope(): void
    {
        $this->unitFilter = $this->currentUnitId();
        $this->unit_id = $this->currentUnitId();
    }

    /**
     * Cegah filter unit di tabel diubah lewat manipulasi request (mis.
     * wire:model di devtools). Dipanggil Livewire setelah properti
     * ter-update, jadi kita paksa balik ke unit sendiri di sini.
     */
    public function updatedUnitFilter(): void
    {
        $this->unitFilter = $this->currentUnitId();
    }

    public function updatedUnitId(): void
    {
        $this->unit_id = $this->currentUnitId();
    }

    /**
     * BUGFIX (sama seperti Unit\Inventory\Index::openCreateModal()): induk
     * (openModal -> resetForm) mereset unit_id balik ke null tiap kali
     * modal "Tambah Aset" dibuka. Kunci ulang segera setelah modal dibuka
     * supaya dropdown Unit Usaha langsung terisi unit sendiri, bukan
     * kosong / opsi "Aset Pusat" yang sebetulnya tidak berlaku untuk
     * Unit Admin.
     */
    public function openModal()
    {
        parent::openModal();
        $this->lockUnitScope();
    }

    public function edit($id)
    {
        // Guard IDOR: pastikan aset ini memang milik unit sendiri sebelum
        // logic asli (yang tidak scoped) dari induk dijalankan.
        Asset::where('unit_id', $this->currentUnitId())->findOrFail($id);

        parent::edit($id);
    }

    public function delete($id)
    {
        Asset::where('unit_id', $this->currentUnitId())->findOrFail($id);

        parent::delete($id);
    }

    public function save()
    {
        $this->lockUnitScope();

        if ($this->editingId) {
            Asset::where('unit_id', $this->currentUnitId())->findOrFail($this->editingId);
        }

        parent::save();
    }

    public function bulkDelete()
    {
        // Buang ID yang bukan milik unit sendiri sebelum diteruskan ke induk.
        $this->selectedRows = Asset::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkDelete();
    }

    public function bulkUpdateStatus($status)
    {
        $this->selectedRows = Asset::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkUpdateStatus($status);
    }

    public function exportSelected()
    {
        // exportData() (tombol "Export Excel" tanpa seleksi) TIDAK perlu
        // di-override: sudah otomatis ter-scope karena $filters['unitFilter']
        // di induk memakai $this->unitFilter yang sudah kita kunci di atas.
        // exportSelected() beda -- dia export berdasarkan daftar ID mentah,
        // jadi butuh guard yang sama seperti bulkDelete()/bulkUpdateStatus().
        $this->selectedRows = Asset::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        return parent::exportSelected();
    }

    /**
     * render() DIPANGGIL hanya untuk menimpa data dropdown 'units' yang
     * dikirim induk (SEMUA unit usaha) menjadi cuma unit sendiri --
     * mencegah admin unit melihat nama unit usaha lain di dropdown filter
     * maupun dropdown form, persis pola yang sama dipakai
     * Unit\Transactions\Index & Unit\Inventory\Index. Query aset, KPI, dan
     * paginasi TIDAK disentuh di sini karena semuanya sudah otomatis
     * ter-scope lewat $this->unitFilter yang dikunci di atas.
     */
    public function render()
    {
        return parent::render()->with('units', Unit::where('id', $this->currentUnitId())->get());
    }
}
