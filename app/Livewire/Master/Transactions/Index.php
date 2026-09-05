<?php

namespace App\Livewire\Master\Transactions;

use App\Exports\TransactionTemplateExport;
use App\Imports\TransactionsImport;
use App\Models\AuditLog; // <-- 1. Import Model AuditLog
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Support\Concerns\SyncsAlertNotifications;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionExport;

#[Layout('components.layouts.app')]
#[Title('Monitoring Transaksi')]
class Index extends Component
{
    use WithPagination, WithFileUploads, SyncsAlertNotifications;

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
    public bool $showErrorModal = false;
    public array $importErrors = [];
    public $excel_file = null;

    public int $perPage = 15;

    // =========================================================================
    // STATE MODAL KELOLA KATEGORI TRANSAKSI
    // =========================================================================
    // Pola & penamaan sengaja dibuat mirip Master\Inventory\Index (modal
    // "Kelola Kategori" produk), bedanya kategori transaksi bisa custom
    // untuk BEBERAPA unit saja (scope 'specific' + category_unit_ids) atau
    // untuk SEMUA unit sekaligus (scope 'all') -- bukan cuma satu unit_id
    // seperti kategori produk. Lihat App\Models\FinanceCategory.
    public bool $showCategoryModal = false;
    public bool $isEditingCategory = false;
    public ?int $editingCategoryId = null;
    public string $category_name = '';
    public string $category_type = 'income'; // 'income' atau 'expense'
    public string $category_scope = 'specific'; // 'all' atau 'specific'
    public array $category_unit_ids = [];

    public function mount(): void
    {
        // Sinkronisasi notifikasi transaksi pending saat halaman dibuka
        $this->syncAllTransactionNotifications();
    }

    // Lifecycle Hooks Reset Page
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingUnitFilter(): void { $this->resetPage(); }
    public function updatingStartDate(): void { $this->resetPage(); }
    public function updatingEndDate(): void { $this->resetPage(); }
    public function updatingPerPage(): void { $this->resetPage(); }

    public function updatedFormUnitId(): void { $this->form_finance_category_id = null; }
    public function updatedFormType(): void { $this->form_finance_category_id = null; }

    // =========================================================================
    // NOTIFIKASI TRANSAKSI PENDING
    // =========================================================================

    /**
     * Jalankan pengecekan notifikasi untuk transaksi yang RELEVAN saja.
     * Dipanggil saat halaman dimuat & setelah proses import,
     * karena keduanya bisa memengaruhi banyak data sekaligus.
     *
     * Sebelumnya method ini menarik SELURUH tabel transaksi
     * (FinanceTransaction::all()) setiap kali halaman dibuka. Sekarang
     * hanya transaksi berstatus 'pending', ATAU transaksi yang masih
     * memiliki notifikasi alert aktif (perlu dicek untuk dibersihkan),
     * yang diperiksa ulang.
     */
    private function syncAllTransactionNotifications(): void
    {
        $pendingIds = FinanceTransaction::query()
            ->where('status', 'pending')
            ->pluck('id');

        $pendingAlertIds = $this->idsWithPendingAlerts(
            idField: 'transaction_id',
            typeField: 'transaction_alert_type',
            typeValues: ['pending_transaction'],
        );

        $relevantIds = $pendingIds->merge($pendingAlertIds)->unique();

        if ($relevantIds->isEmpty()) {
            return;
        }

        FinanceTransaction::query()->whereIn('id', $relevantIds)->get()->each(function (FinanceTransaction $transaction) {
            $this->syncTransactionNotification($transaction);
        });
    }

    /**
     * Sinkronisasi status notifikasi untuk SATU transaksi:
     * - Kirim notifikasi baru jika status 'pending' dan belum ada notifikasi aktif.
     * - Tandai notifikasi lama sebagai dibaca jika status sudah completed/cancelled.
     */
    private function syncTransactionNotification(FinanceTransaction $transaction): void
    {
        $transaction->refresh();

        if ($transaction->status === 'pending') {
            $typeText = $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran';

            $this->fireTransactionAlert(
                $transaction,
                'pending_transaction',
                'Transaksi Menunggu Konfirmasi',
                "Transaksi {$typeText} No. Ref {$transaction->reference_no} sebesar Rp " . number_format($transaction->amount, 0, ',', '.') . " berstatus pending dan butuh tindak lanjut."
            );
        } else {
            $this->clearTransactionAlert($transaction, 'pending_transaction');
        }
    }

    private function fireTransactionAlert(FinanceTransaction $transaction, string $type, string $title, string $message): void
    {
        $this->batchFireAlert(
            idField: 'transaction_id',
            idValue: $transaction->id,
            typeField: 'transaction_alert_type',
            typeValue: $type,
            title: $title,
            message: $message,
            badge: 'Pending',
            extraData: [
                'transaction_id'         => $transaction->id,
                'transaction_alert_type' => $type,
            ],
        );
    }

    private function clearTransactionAlert(FinanceTransaction $transaction, string $type): void
    {
        $this->batchClearAlert(
            idField: 'transaction_id',
            idValue: $transaction->id,
            typeField: 'transaction_alert_type',
            typeValue: $type,
        );
    }

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
                // PERUBAHAN: kategori tidak lagi terikat ke SATU unit_id
                // saja (lihat migrasi finance_categories & FinanceCategory
                // model), jadi validasi Rule::exists()->where('unit_id', ...)
                // yang lama sudah tidak berlaku. Kategori dianggap valid
                // untuk baris ini kalau tipe-nya cocok DAN kategori tsb.
                // berlaku untuk form_unit_id yang dipilih -- baik karena
                // scope-nya 'all' (semua unit) maupun 'specific' yang
                // mencakup unit ini (lihat FinanceCategory::appliesToUnit()).
                function ($attribute, $value, $fail) {
                    $category = FinanceCategory::find($value);

                    if (
                        ! $category
                        || strtolower($category->type) !== strtolower((string) $this->form_type)
                        || ! $category->appliesToUnit((int) $this->form_unit_id)
                    ) {
                        $fail('Kategori Transaksi yang dipilih tidak berlaku untuk Unit Usaha dan Tipe Transaksi ini.');
                    }
                },
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
            $oldValues = $transaction->getAttributes();

            if ($this->form_proof_file) {
                if ($transaction->proof_file && Storage::disk('public')->exists($transaction->proof_file)) {
                    Storage::disk('public')->delete($transaction->proof_file);
                }
                $data['proof_file'] = $this->form_proof_file->store('proofs', 'public');
            }

            $transaction->update($data);

            // Audit Log: Edit Transaksi
            AuditLog::record(
                event: 'TRANSACTION_UPDATED',
                identifier: $transaction->reference_no,
                description: "Admin memperbarui data transaksi No. Ref: {$transaction->reference_no}",
                oldValues: $oldValues,
                newValues: $transaction->getAttributes()
            );

            session()->flash('message', 'Transaksi berhasil diperbarui.');
        } else {
            if ($this->form_proof_file) {
                $data['proof_file'] = $this->form_proof_file->store('proofs', 'public');
            }

            $transaction = FinanceTransaction::create($data);

            // Audit Log: Transaksi Baru
            $typeText = $transaction->type === 'income' ? 'Pemasukan' : 'Pengeluaran';
            AuditLog::record(
                event: 'TRANSACTION_CREATED',
                identifier: $transaction->reference_no,
                description: "Admin mencatat transaksi {$typeText} baru: No. Ref {$transaction->reference_no} sebesar Rp " . number_format($transaction->amount, 0, ',', '.'),
                newValues: $transaction->getAttributes()
            );

            session()->flash('message', 'Transaksi baru berhasil dicatat.');
        }

        // Sinkronisasi notifikasi status pending
        $this->syncTransactionNotification($transaction);

        $this->closeCreateModal();
    }

    // =========================================================================
    // MANAJEMEN KATEGORI TRANSAKSI (LOGIC MODAL)
    // =========================================================================
    // Modul "Kelola Kategori" transaksi, sengaja ditaruh menyatu di dalam
    // menu Transaksi (bukan menu tersendiri) -- persis seperti pola
    // "Kelola Kategori" pada Master\Inventory\Index untuk produk.

    public function openCategoryModal(): void
    {
        $this->resetCategoryForm();

        // Prefill dari form transaksi yang sedang aktif (kalau ada) supaya
        // admin tidak perlu isi ulang unit/tipe yang sudah dipilih.
        if ($this->form_unit_id) {
            $this->category_unit_ids = [(int) $this->form_unit_id];
        }
        if ($this->form_type) {
            $this->category_type = $this->form_type;
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
        $this->reset(['category_name', 'category_unit_ids', 'editingCategoryId', 'isEditingCategory']);
        $this->category_type = 'income';
        $this->category_scope = 'specific';
        $this->resetErrorBag(['category_name', 'category_type', 'category_scope', 'category_unit_ids']);
    }

    public function saveCategory(): void
    {
        $this->validate([
            'category_name'        => 'required|string|max:255',
            'category_type'        => 'required|in:income,expense',
            'category_scope'       => 'required|in:all,specific',
            'category_unit_ids'    => 'required_if:category_scope,specific|array|min:1',
            'category_unit_ids.*'  => 'exists:units,id',
        ], [
            'category_name.required'     => 'Nama kategori wajib diisi.',
            'category_type.required'     => 'Tipe kategori wajib dipilih.',
            'category_scope.required'    => 'Cakupan Unit Usaha wajib dipilih.',
            'category_unit_ids.required_if' => 'Pilih minimal satu Unit Usaha untuk kategori khusus.',
            'category_unit_ids.min'      => 'Pilih minimal satu Unit Usaha untuk kategori khusus.',
        ]);

        $isEdit = $this->isEditingCategory;

        // 1. Ambil oldValues SEBELUM data di-update (termasuk unit-unit
        // lama, dicatat manual karena bukan kolom langsung pada tabel).
        $oldCategory = $isEdit ? FinanceCategory::with('units')->find($this->editingCategoryId) : null;
        $oldValues = $oldCategory ? array_merge(
            $oldCategory->getAttributes(),
            ['unit_ids' => $oldCategory->units->pluck('id')->all()]
        ) : null;

        // 2. Simpan data utama kategori
        $category = FinanceCategory::updateOrCreate(
            ['id' => $this->editingCategoryId],
            [
                'name'  => $this->category_name,
                'type'  => $this->category_type,
                'scope' => $this->category_scope,
            ]
        );

        // 3. Sinkronkan pivot unit. Kategori berscope 'all' tidak perlu
        // baris pivot sama sekali (otomatis berlaku ke semua unit), jadi
        // pivot lama (kalau sebelumnya 'specific') dikosongkan.
        $category->units()->sync(
            $this->category_scope === 'all' ? [] : $this->category_unit_ids
        );

        // Kalau modal ini dibuka dari tengah form "Tambah/Edit Transaksi"
        // dan kategori yang baru disimpan cocok dengan Unit & Tipe yang
        // sedang dipilih di form itu, langsung pilihkan otomatis.
        if (
            $this->showCreateModal
            && $this->form_type === $category->type
            && $category->fresh('units')->appliesToUnit((int) $this->form_unit_id)
        ) {
            $this->form_finance_category_id = $category->id;
        }

        // 4. Catat Audit Log
        AuditLog::record(
            event: $isEdit ? 'FINANCE_CATEGORY_UPDATED' : 'FINANCE_CATEGORY_CREATED',
            identifier: (string) $category->id,
            description: $isEdit
                ? "Admin memperbarui kategori transaksi: {$category->name}"
                : "Admin menambahkan kategori transaksi baru: {$category->name}",
            oldValues: $oldValues,
            newValues: array_merge(
                $category->getAttributes(),
                ['unit_ids' => $this->category_scope === 'all' ? [] : $this->category_unit_ids]
            )
        );

        $this->resetCategoryForm();
        session()->flash('category_success', $isEdit ? 'Kategori berhasil diperbarui.' : 'Kategori berhasil ditambahkan.');
    }

    public function editCategory(int $id): void
    {
        $category = FinanceCategory::with('units')->findOrFail($id);

        $this->editingCategoryId = $category->id;
        $this->category_name = $category->name;
        $this->category_type = $category->type;
        $this->category_scope = $category->scope;
        $this->category_unit_ids = $category->units->pluck('id')->map(fn ($id) => (int) $id)->all();
        $this->isEditingCategory = true;
    }

    public function deleteCategory(int $id): void
    {
        $category = FinanceCategory::findOrFail($id);

        if ($category->transactions()->count() > 0) {
            session()->flash('category_error', 'Kategori tidak dapat dihapus karena masih digunakan oleh transaksi.');
            return;
        }

        AuditLog::record(
            event: 'FINANCE_CATEGORY_DELETED',
            identifier: (string) $category->id,
            description: "Admin menghapus kategori transaksi: {$category->name}",
            oldValues: $category->getAttributes(),
            newValues: null
        );

        // Bersihkan pivot unit sebelum kategori dihapus (foreign key sudah
        // cascadeOnDelete di migrasi, baris ini murni jaga-jaga eksplisit).
        $category->units()->detach();
        $category->delete();

        session()->flash('category_success', 'Kategori berhasil dihapus.');
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
            $oldProof = $this->selectedTransaction->proof_file;

            if ($oldProof && Storage::disk('public')->exists($oldProof)) {
                Storage::disk('public')->delete($oldProof);
            }

            $path = $this->proofFile->store('proofs', 'public');
            $this->selectedTransaction->update(['proof_file' => $path]);
            $this->selectedTransaction->refresh();

            // Audit Log: Unggah Bukti
            AuditLog::record(
                event: 'TRANSACTION_PROOF_UPLOADED',
                identifier: $this->selectedTransaction->reference_no,
                description: "Admin mengunggah bukti pembayaran transaksi No. Ref: {$this->selectedTransaction->reference_no}",
                oldValues: ['proof_file' => $oldProof],
                newValues: ['proof_file' => $path]
            );

            $this->reset(['proofFile']);
            session()->flash('message', 'Bukti transaksi berhasil diunggah.');
        }
    }

    public function deleteProof(): void
    {
        if ($this->selectedTransaction && $this->selectedTransaction->proof_file) {
            $oldProof = $this->selectedTransaction->proof_file;

            if (Storage::disk('public')->exists($oldProof)) {
                Storage::disk('public')->delete($oldProof);
            }

            $this->selectedTransaction->update(['proof_file' => null]);
            $this->selectedTransaction->refresh();

            // Audit Log: Hapus Bukti
            AuditLog::record(
                event: 'TRANSACTION_PROOF_DELETED',
                identifier: $this->selectedTransaction->reference_no,
                description: "Admin menghapus berkas bukti pembayaran transaksi No. Ref: {$this->selectedTransaction->reference_no}",
                oldValues: ['proof_file' => $oldProof],
                newValues: null
            );

            session()->flash('message', 'Bukti transaksi berhasil dihapus.');
        }
    }

    // Bulk Action Methods
    public function bulkDelete(): void
    {
        if (empty($this->selectedRows)) return;

        $transactions = FinanceTransaction::whereIn('id', $this->selectedRows)->get();

        foreach ($transactions as $transaction) {
            // Audit Log: Hapus Per Transaksi
            AuditLog::record(
                event: 'TRANSACTION_DELETED',
                identifier: $transaction->reference_no ?? "ID: {$transaction->id}",
                description: "Admin menghapus transaksi No. Ref: {$transaction->reference_no}",
                oldValues: $transaction->getAttributes(),
                newValues: null
            );

            // Bersihkan notifikasi pending terkait transaksi yang akan dihapus
            $this->clearTransactionAlert($transaction, 'pending_transaction');

            if ($transaction->proof_file && Storage::disk('public')->exists($transaction->proof_file)) {
                Storage::disk('public')->delete($transaction->proof_file);
            }

            $transaction->delete();
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Transaksi terpilih berhasil dihapus.');
    }

    public function bulkUpdateStatus(string $status): void
    {
        if (empty($this->selectedRows)) return;

        $transactions = FinanceTransaction::whereIn('id', $this->selectedRows)->get();

        foreach ($transactions as $transaction) {
            $oldStatus = $transaction->status;
            
            if ($oldStatus !== $status) {
                $transaction->update(['status' => $status]);

                // Audit Log: Update Status Massal
                AuditLog::record(
                    event: 'TRANSACTION_STATUS_UPDATED',
                    identifier: $transaction->reference_no,
                    description: "Admin mengubah status transaksi {$transaction->reference_no} dari '{$oldStatus}' menjadi '{$status}'",
                    oldValues: ['status' => $oldStatus],
                    newValues: ['status' => $status]
                );
            }

            // Sinkronisasi notifikasi (baik status berubah maupun tidak, aman untuk dijalankan)
            $this->syncTransactionNotification($transaction);
        }

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

    public function closeErrorModal(): void
    {
        $this->showErrorModal = false;
        $this->importErrors = [];
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

        $fileName = $this->excel_file->getClientOriginalName();
        $import = new TransactionsImport();
        Excel::import($import, $this->excel_file->getRealPath());

        // Cek jika terdapat kegagalan validasi baris dari Excel
        if ($import->failures()->isNotEmpty()) {
            $this->importErrors = [];
            
            foreach ($import->failures() as $failure) {
                $attr = $failure->attribute();
                $columnName = $import->customValidationAttributes()[$attr] ?? $attr;
                $rowValues  = $failure->values();

                foreach ($failure->errors() as $error) {
                    $this->importErrors[] = [
                        'row'      => $failure->row(),
                        'column'   => $columnName,
                        'value'    => $rowValues[$attr] ?? '(Kosong)',
                        'messages' => $error,
                    ];
                }
            }

            // Audit Log: Impor Gagal
            AuditLog::record(
                event: 'TRANSACTION_IMPORT_FAILED',
                identifier: $fileName,
                description: "Admin gagal melakukan impor data transaksi dari berkas '{$fileName}'. Terdapat kesalahan validasi."
            );

            $this->showImportModal = false;
            $this->showErrorModal = true;
            return;
        }

        // Audit Log: Impor Sukses
        AuditLog::record(
            event: 'TRANSACTION_IMPORTED',
            identifier: $fileName,
            description: "Admin berhasil mengimpor data transaksi melalui berkas Excel",
            newValues: [
                'nama_file'   => $fileName,
                'ukuran_file' => round($this->excel_file->getSize() / 1024, 2) . ' KB',
                'waktu_impor' => now()->translatedFormat('d F Y H:i:s'),
            ]
        );

        // Sinkronisasi notifikasi karena import bisa menambahkan banyak transaksi pending sekaligus
        $this->syncAllTransactionNotifications();

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

    public function exportData()
    {
        $filters = [
            'search'      => $this->search,
            'unitFilter'  => $this->unitFilter,
            'typeFilter'  => $this->typeFilter,
            'statusFilter'=> $this->statusFilter,
            'startDate'   => $this->startDate,
            'endDate'     => $this->endDate,
        ];

        $fileName = 'Data_Transaksi_' . now()->format('Ymd_His') . '.xlsx';

        // Audit Log: Export Data
        AuditLog::record(
            event: 'TRANSACTION_EXPORTED',
            identifier: $fileName,
            description: "Admin mengekspor data transaksi ke Excel" . 
                ($this->unitFilter ? " (Unit ID: {$this->unitFilter})" : '') .
                ($this->startDate || $this->endDate ? " periode {$this->startDate} s/d {$this->endDate}" : ''),
        );

        return Excel::download(new TransactionExport($filters), $fileName);
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
            ->paginate($this->perPage);

        $categories = $this->form_unit_id
            ? FinanceCategory::query()
                ->forUnit((int) $this->form_unit_id)
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