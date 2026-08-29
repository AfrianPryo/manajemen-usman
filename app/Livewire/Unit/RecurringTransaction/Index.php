<?php

namespace App\Livewire\Unit\RecurringTransaction;

use App\Livewire\Master\RecurringTransaction\Index as MasterRecurringTransactionIndex;
use App\Models\FinanceCategory;
use App\Models\RecurringTransaction;
use App\Models\Unit;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Transaksi Berulang".
 *
 * BEDA dengan Transactions/Inventory: class induk (Master\RecurringTransaction\Index)
 * TIDAK punya property filter unit di level tabel (Master memang menampilkan
 * transaksi berulang dari SEMUA unit tanpa filter), dan query builder-nya
 * (getRecurringTransactionsQuery, getAvailableCategories) bersifat `private`
 * sehingga tidak bisa dipanggil ulang dari sini.
 *
 * Karena itu render() & updatedSelectAll() di-tulis ulang penuh di sini
 * (bukan cuma dikunci lewat property seperti dua modul sebelumnya), supaya
 * daftar & pilihan "select all" benar-benar terbatas pada unit sendiri.
 * Method berbasis ID tetap di-guard sebagai lapis kedua.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterRecurringTransactionIndex
{
    use ScopedToUnit;

    public function mount()
    {
        parent::mount();
        $this->unit_id = $this->currentUnitId();
    }

    /**
     * BUGFIX (sama seperti Unit\Transactions\Index & Unit\Inventory\Index):
     * induk (openModal -> resetForm) mereset unit_id balik ke null setiap
     * kali modal "Tambah Transaksi Berulang" dibuka. Kunci ulang di sini
     * supaya dropdown Unit Usaha langsung terisi unit sendiri (bukan
     * kosong) dan daftar Kategori Keuangan di render() -- yang bergantung
     * ke unit_id -- langsung ikut muncul.
     */
    public function openModal()
    {
        parent::openModal();
        $this->unit_id = $this->currentUnitId();
    }

    public function updatedUnitId($value)
    {
        // Form Unit hanya berisi unit sendiri (lihat catatan blade), tapi
        // tetap dikunci ulang di sini untuk jaga-jaga manipulasi request.
        $this->unit_id = $this->currentUnitId();
        $this->finance_category_id = '';
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->unitScopedQuery()
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function bulkUpdateStatus($status)
    {
        $this->selectedRows = RecurringTransaction::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkUpdateStatus($status);
    }

    public function bulkDelete()
    {
        $this->selectedRows = RecurringTransaction::where('unit_id', $this->currentUnitId())
            ->whereIn('id', $this->selectedRows)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        parent::bulkDelete();
    }

    public function save()
    {
        $this->unit_id = $this->currentUnitId();

        if ($this->editingId) {
            RecurringTransaction::where('unit_id', $this->currentUnitId())->findOrFail($this->editingId);
        }

        parent::save();
    }

    public function edit($id)
    {
        RecurringTransaction::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::edit($id);
    }

    public function toggleStatus($id)
    {
        RecurringTransaction::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::toggleStatus($id);
    }

    public function delete($id)
    {
        RecurringTransaction::where('unit_id', $this->currentUnitId())->findOrFail($id);
        parent::delete($id);
    }

    /**
     * Duplikat ringkas dari getRecurringTransactionsQuery() milik induk
     * (private, tidak bisa dipanggil dari sini) — HANYA ditambah satu
     * klausa ->where('unit_id', ...). Kalau filter/pencarian di induk
     * berubah, method ini WAJIB disamakan lagi secara manual.
     */
    private function unitScopedQuery()
    {
        return RecurringTransaction::with(['unit', 'category'])
            ->where('unit_id', $this->currentUnitId())
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest();
    }

    public function render()
    {
        $categories = $this->unit_id
            ? FinanceCategory::where('unit_id', $this->unit_id)
                ->where('type', $this->type)
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.master.recurring-transaction.index', [
            'units' => Unit::where('id', $this->currentUnitId())->get(),
            'categories' => $categories,
            'recurringTransactions' => $this->unitScopedQuery()->paginate($this->perPage),
        ]);
    }
}