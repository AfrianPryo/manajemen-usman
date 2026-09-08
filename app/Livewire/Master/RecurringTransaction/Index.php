<?php

namespace App\Livewire\Master\RecurringTransaction;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Unit;
use App\Models\RecurringTransaction;
use App\Models\FinanceCategory;
use App\Models\AuditLog;
use App\Services\RecurringTransactionService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manajemen Transaksi Berulang')]
class Index extends Component
{
    use WithPagination;

    // Filter & Pagination
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $perPage = 10;

    // Bulk Action Selection
    public $selectedRows = [];
    public $selectAll = false;

    // Modal & Form State
    public $showModal = false;
    public $editingId = null;

    public $title = '';
    public $unit_id = '';
    public $finance_category_id = '';
    public $type = 'income';
    public $amount = '';
    public $frequency = 'monthly';
    public $start_date = '';
    public $end_date = null;
    public $next_run_date = '';
    public $auto_approve = true;
    public $notes = '';

    public function rules()
    {
        return [
            'title'               => 'required|string|max:255',
            'unit_id'             => 'required|exists:units,id',
            'finance_category_id' => 'required|exists:finance_categories,id',
            'type'                => ['required', Rule::in(['income', 'expense'])],
            'amount'              => 'required|numeric|min:0',
            'frequency'           => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date'          => 'required|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'auto_approve'        => 'boolean',
            'notes'               => 'nullable|string',
        ];
    }

    public function mount(RecurringTransactionService $recurringTransactionService)
    {
        $this->start_date = date('Y-m-d');
        $this->next_run_date = date('Y-m-d');

        // Jalankan pengecekan transaksi yang jatuh tempo saat halaman diakses.
        // Implementasi sesungguhnya sudah dipindah & disatukan ke
        // App\Services\RecurringTransactionService (dipakai bareng oleh
        // command terjadwal `recurring:process`), supaya tidak ada dua
        // salinan logika yang bisa saling tidak sinkron, dan N+1 query-nya
        // (User::all() + exists() per pasangan user+item) cukup diperbaiki
        // di satu tempat -- lihat docblock di service tersebut untuk detail.
        $recurringTransactionService->processDueTransactions();
    }

    // Reset halaman ketika filter atau pencarian berubah
    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingTypeFilter() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    /**
     * Saat Unit Usaha diganti di form, reset kategori yang sudah dipilih
     * supaya tidak nyangkut kategori milik unit lain.
     */
    public function updatedUnitId($value)
    {
        $this->finance_category_id = '';
    }

    /**
     * Saat Tipe Transaksi (income/expense) diganti, reset kategori juga,
     * karena kategori terikat pada tipe (income atau expense).
     */
    public function updatedType($value)
    {
        $this->finance_category_id = '';
    }

    // Logic Pilih Semua Data
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getRecurringTransactionsQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // Actions untuk Bulk Action
    public function bulkUpdateStatus($status)
    {
        if (!in_array($status, ['active', 'paused'])) return;

        $items = RecurringTransaction::whereIn('id', $this->selectedRows)->get(['id', 'title', 'status']);

        RecurringTransaction::whereIn('id', $this->selectedRows)->update([
            'status' => $status
        ]);

        AuditLog::record(
            'RECURRING_TRANSACTION_BULK_STATUS_UPDATE',
            null,
            count($items) . " transaksi berulang diubah statusnya menjadi '{$status}': " . $items->pluck('title')->implode(', '),
            ['statuses' => $items->pluck('status', 'title')->all()],
            ['status' => $status]
        );

        $this->selectedRows = [];
        $this->selectAll = false;

        session()->flash('message', 'Status data terpilih berhasil diperbarui.');
    }

    public function bulkDelete()
    {
        $items = RecurringTransaction::whereIn('id', $this->selectedRows)->get(['id', 'title']);

        RecurringTransaction::whereIn('id', $this->selectedRows)->delete();

        AuditLog::record(
            'RECURRING_TRANSACTION_BULK_DELETE',
            null,
            count($items) . ' transaksi berulang dihapus: ' . $items->pluck('title')->implode(', '),
            null,
            ['ids' => $items->pluck('id')->all(), 'titles' => $items->pluck('title')->all()]
        );

        $this->selectedRows = [];
        $this->selectAll = false;

        session()->flash('message', 'Data terpilih berhasil dihapus.');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['title', 'unit_id', 'finance_category_id', 'type', 'amount', 'frequency', 'end_date', 'notes', 'editingId']);
        $this->start_date = date('Y-m-d');
        $this->auto_approve = true;
        $this->resetValidation();
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $transaction = RecurringTransaction::findOrFail($this->editingId);
            $oldValues = $transaction->toArray();
            $transaction->update($validated);

            AuditLog::record(
                'RECURRING_TRANSACTION_UPDATE',
                $transaction->title,
                "Transaksi berulang '{$transaction->title}' diperbarui.",
                $oldValues,
                $transaction->fresh()->toArray()
            );

            session()->flash('message', 'Transaksi berulang berhasil diperbarui.');
        } else {
            $validated['status'] = 'active';
            $validated['next_run_date'] = $this->start_date;
            $transaction = RecurringTransaction::create($validated);

            AuditLog::record(
                'RECURRING_TRANSACTION_CREATE',
                $transaction->title,
                "Transaksi berulang baru '{$transaction->title}' dibuat.",
                null,
                $transaction->toArray()
            );

            session()->flash('message', 'Transaksi berulang berhasil dibuat.');
        }

        $this->closeModal();
    }

    public function edit($id)
    {
        $item = RecurringTransaction::findOrFail($id);
        $this->editingId = $item->id;
        $this->title = $item->title;
        $this->unit_id = $item->unit_id;
        $this->type = $item->type;
        $this->finance_category_id = $item->finance_category_id;
        $this->amount = $item->amount;
        $this->frequency = $item->frequency;
        $this->start_date = $item->start_date;
        $this->end_date = $item->end_date;
        $this->auto_approve = $item->auto_approve;
        $this->notes = $item->notes;

        $this->showModal = true;
    }

    public function toggleStatus($id)
    {
        $item = RecurringTransaction::findOrFail($id);
        $oldStatus = $item->status;
        $item->status = $item->status === 'active' ? 'paused' : 'active';
        $item->save();

        AuditLog::record(
            'RECURRING_TRANSACTION_STATUS_TOGGLE',
            $item->title,
            "Status transaksi berulang '{$item->title}' diubah dari '{$oldStatus}' menjadi '{$item->status}'.",
            ['status' => $oldStatus],
            ['status' => $item->status]
        );

        session()->flash('message', 'Status transaksi berhasil diubah.');
    }

    public function delete($id)
    {
        $item = RecurringTransaction::findOrFail($id);
        $oldValues = $item->toArray();
        $item->delete();

        AuditLog::record(
            'RECURRING_TRANSACTION_DELETE',
            $item->title,
            "Transaksi berulang '{$item->title}' dihapus.",
            $oldValues,
            null
        );

        session()->flash('message', 'Transaksi berulang berhasil dihapus.');
    }

    private function getRecurringTransactionsQuery()
    {
        return RecurringTransaction::with(['unit', 'category'])
            ->when($this->search, fn($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn($query) => $query->where('status', $this->statusFilter))
            ->when($this->typeFilter, fn($query) => $query->where('type', $this->typeFilter))
            ->latest();
    }

    /**
     * Kategori yang ditampilkan di dropdown form, difilter berdasarkan
     * Unit Usaha dan Tipe Transaksi yang sedang dipilih di form.
     * Ini yang mencegah duplikasi tampilan (sebelumnya menampilkan
     * SEMUA kategori dari SEMUA unit sekaligus).
     */
    private function getAvailableCategories()
    {
        if (!$this->unit_id) {
            return collect();
        }

        return FinanceCategory::where('unit_id', $this->unit_id)
            ->where('type', $this->type)
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        return view('livewire.master.recurring-transaction.index', [
            'units' => Unit::orderBy('name')->get(),
            'categories' => $this->getAvailableCategories(),
            'recurringTransactions' => $this->getRecurringTransactionsQuery()->paginate($this->perPage),
        ]);
    }
}