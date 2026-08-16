<?php

namespace App\Livewire\Master\Transactions;

use App\Exports\TransactionTemplateExport;
use App\Imports\TransactionsImport;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
#[Title('Monitoring Transaksi')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    // Filter Properties
    public string $search = '';
    public string $typeFilter = '';
    public string $statusFilter = '';
    public ?int $unitFilter = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Selection & Bulk Action Properties
    public array $selectedRows = [];
    public bool $selectAll = false;

    // Detail Modal Properties
    public bool $showDetailModal = false;
    public ?FinanceTransaction $selectedTransaction = null;
    public $proofFile = null;

    // Form Modal Properties (Create & Edit)
    public bool $showCreateModal = false;
    public bool $isEditing = false;
    public ?int $editingTransactionId = null;

    public string $form_type = 'income'; // 'income' or 'expense'
    public ?int $form_unit_id = null;
    public ?int $form_finance_category_id = null;
    public ?string $form_transaction_date = null;
    public ?string $form_reference_no = null;
    public string $form_payment_method = 'cash';
    public string $form_status = 'completed';
    public $form_amount = null;
    public ?string $form_description = null;
    public $form_proof_file = null;

    // Import Properties
    public bool $showImportModal = false;
    public $excel_file = null;

    // Lifecycle Hooks Reset Page
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingUnitFilter(): void { $this->resetPage(); }
    public function updatingStartDate(): void { $this->resetPage(); }
    public function updatingEndDate(): void { $this->resetPage(); }

    public function updatedFormUnitId(): void { $this->form_finance_category_id = null; }
    public function updatedFormType(): void { $this->form_finance_category_id = null; }

    public function updatedSelectAll($value): void
    {
        if ($value) {
            $this->selectedRows = $this->getTransactionsQuery()->pluck('id')->map(fn($id) => (string)$id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'statusFilter', 'unitFilter', 'startDate', 'endDate']);
        $this->resetPage();
    }

    // Modal Create & Edit Methods
    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->isEditing = false;
        $this->editingTransactionId = null;
        $this->form_transaction_date = now()->format('Y-m-d');
        $this->form_reference_no = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        $this->showCreateModal = true;
    }

    public function editTransaction(int $id): void
    {
        $this->resetValidation();
        $transaction = FinanceTransaction::findOrFail($id);

        $this->isEditing = true;
        $this->editingTransactionId = $transaction->id;
        $this->form_type = $transaction->type;
        $this->form_unit_id = $transaction->unit_id;
        $this->form_finance_category_id = $transaction->finance_category_id;
        $this->form_transaction_date = optional($transaction->transaction_date)->format('Y-m-d');
        $this->form_reference_no = $transaction->reference_no;
        $this->form_payment_method = $transaction->payment_method ?? 'cash';
        $this->form_status = $transaction->status ?? 'completed';
        $this->form_amount = $transaction->amount;
        $this->form_description = $transaction->description;

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function resetCreateForm(): void
    {
        $this->reset([
            'isEditing', 'editingTransactionId', 'form_type', 'form_unit_id', 
            'form_finance_category_id', 'form_transaction_date', 'form_reference_no', 
            'form_payment_method', 'form_status', 'form_amount', 'form_description', 'form_proof_file'
        ]);
        $this->resetValidation();
    }

    public function saveTransaction(): void
    {
        $validated = $this->validate([
            'form_type' => 'required|in:income,expense',
            'form_unit_id' => 'required|exists:units,id',
            'form_finance_category_id' => [
                'required',
                Rule::exists('finance_categories', 'id')->where(function ($query) {
                    $query->where('unit_id', $this->form_unit_id)
                        ->where('type', $this->form_type);
                }),
            ],
            'form_transaction_date' => 'required|date',
            'form_reference_no' => 'nullable|string|max:50',
            'form_payment_method' => 'required|string',
            'form_status' => 'required|in:completed,pending,cancelled',
            'form_amount' => 'required|numeric|min:1',
            'form_description' => 'nullable|string|max:500',
            'form_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [], [
            'form_unit_id' => 'Unit Usaha',
            'form_finance_category_id' => 'Kategori Transaksi',
            'form_transaction_date' => 'Tanggal Transaksi',
            'form_amount' => 'Jumlah Nominal',
        ]);

        $data = [
            'unit_id' => $this->form_unit_id,
            'finance_category_id' => $this->form_finance_category_id,
            'user_id' => auth()->id(),
            'reference_no' => $this->form_reference_no ?: 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'type' => $this->form_type,
            'status' => $this->form_status,
            'payment_method' => $this->form_payment_method,
            'amount' => $this->form_amount,
            'description' => $this->form_description,
            'transaction_date' => $this->form_transaction_date,
        ];

        if ($this->isEditing) {
            $transaction = FinanceTransaction::findOrFail($this->editingTransactionId);

            if ($this->form_proof_file) {
                if ($transaction->proof_file && Storage::disk('public')->exists($transaction->proof_file)) {
                    Storage::disk('public')->delete($transaction->proof_file);
                }
                $data['proof_file'] = $this->form_proof_file->store('proofs', 'public');
            }

            $transaction->update($data);
            session()->flash('message', 'Transaksi berhasil diperbarui.');
        } else {
            if ($this->form_proof_file) {
                $data['proof_file'] = $this->form_proof_file->store('proofs', 'public');
            }

            FinanceTransaction::create($data);
            session()->flash('message', 'Transaksi baru berhasil dicatat.');
        }

        $this->closeCreateModal();
    }

    // 1. Tambahkan properti $perPage di bagian properti class
    public int $perPage = 15;

    // 2. Tambahkan lifecycle hook reset halaman saat perPage berubah
    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    // Detail Modal Methods
    public function openDetail(int $id): void
    {
        $this->resetValidation();
        $this->reset(['proofFile']);
        $this->selectedTransaction = FinanceTransaction::with(['unit', 'category', 'user'])->find($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
        $this->reset(['proofFile']);
    }

    public function uploadProof(): void
    {
        $this->validate([
            'proofFile' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:3072',
        ], [
            'proofFile.required' => 'Pilih berkas terlebih dahulu.',
            'proofFile.mimes' => 'Format berkas harus JPG, PNG, WEBP, atau PDF.',
            'proofFile.max' => 'Ukuran berkas maksimal 3 MB.',
        ]);

        if ($this->selectedTransaction) {
            if ($this->selectedTransaction->proof_file && Storage::disk('public')->exists($this->selectedTransaction->proof_file)) {
                Storage::disk('public')->delete($this->selectedTransaction->proof_file);
            }

            $path = $this->proofFile->store('proofs', 'public');
            $this->selectedTransaction->update(['proof_file' => $path]);
            $this->selectedTransaction->refresh();

            $this->reset(['proofFile']);
            session()->flash('message', 'Bukti transaksi berhasil diunggah.');
        }
    }

    public function deleteProof(): void
    {
        if ($this->selectedTransaction && $this->selectedTransaction->proof_file) {
            if (Storage::disk('public')->exists($this->selectedTransaction->proof_file)) {
                Storage::disk('public')->delete($this->selectedTransaction->proof_file);
            }

            $this->selectedTransaction->update(['proof_file' => null]);
            $this->selectedTransaction->refresh();

            session()->flash('message', 'Bukti transaksi berhasil dihapus.');
        }
    }

    // Bulk Action Methods
    public function bulkDelete(): void
    {
        if (empty($this->selectedRows)) return;

        FinanceTransaction::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Transaksi terpilih berhasil dihapus.');
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selectedRows)) return;

        FinanceTransaction::whereIn('id', $this->selectedRows)->update(['status' => $status]);
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Status transaksi berhasil diperbarui.');
    }

    // Excel Import Methods
    public function openImportModal(): void
    {
        $this->reset('excel_file');
        $this->resetValidation();
        $this->showImportModal = true;
    }

    public function closeImportModal(): void
    {
        $this->showImportModal = false;
        $this->reset('excel_file');
    }

    public function downloadTemplate()
    {
        return Excel::download(new TransactionTemplateExport, 'Template_Import_Transaksi.xlsx');
    }

    public function importExcel(): void
    {
        $this->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [], [
            'excel_file' => 'Berkas Excel'
        ]);

        Excel::import(new TransactionsImport, $this->excel_file->getRealPath());

        $this->closeImportModal();
        session()->flash('message', 'Data transaksi dari Excel berhasil diimpor.');
    }

    private function getTransactionsQuery()
    {
        return FinanceTransaction::with(['unit', 'category', 'user'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', "%{$this->search}%")
                        ->orWhere('reference_no', 'like', "%{$this->search}%");
                });
            })
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate));
    }

    public function render()
    {
        $kpiQuery = FinanceTransaction::query()
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', "%{$this->search}%")
                        ->orWhere('reference_no', 'like', "%{$this->search}%");
                });
            })
            ->when($this->unitFilter, fn ($q) => $q->where('unit_id', $this->unitFilter))
            ->when($this->startDate, fn ($q) => $q->whereDate('transaction_date', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('transaction_date', '<=', $this->endDate));

        $totalIncome  = (clone $kpiQuery)->where('type', 'income')->where('status', 'completed')->sum('amount');
        $totalExpense = (clone $kpiQuery)->where('type', 'expense')->where('status', 'completed')->sum('amount');
        $pendingCount = (clone $kpiQuery)->where('status', 'pending')->count();
        $netBalance   = $totalIncome - $totalExpense;

        $transactions = $this->getTransactionsQuery()
            ->latest('transaction_date')
            ->latest('id')
            ->paginate($this->perPage); // Ganti angka 15 dengan $this->perPage

        $categories = $this->form_unit_id
            ? FinanceCategory::query()
                ->where('unit_id', $this->form_unit_id)
                ->when($this->form_type, function ($q) {
                    $q->whereRaw('LOWER(type) = ?', [strtolower($this->form_type)]);
                })
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.master.transactions.index', compact(
            'transactions', 'totalIncome', 'totalExpense', 'netBalance', 'pendingCount', 'categories'
        ))->with('units', Unit::orderBy('name')->get());
    }
}