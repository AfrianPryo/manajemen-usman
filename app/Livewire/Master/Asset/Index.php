<?php

namespace App\Livewire\Master\Asset;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Exports\AssetTemplateExport;
use App\Exports\AssetExport;
use App\Imports\AssetsImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;

#[Layout('layouts.app')]
#[Title('Manajemen Aset Usaha')]
class Index extends Component
{
    use WithPagination, WithFileUploads;

    // Filter & Search
    public $search = '';
    public $statusFilter = '';
    public $categoryFilter = '';
    public $perPage = 10;

    // Bulk Action Selection
    public $selectedRows = [];
    public $selectAll = false;

    // Modal State
    public $showModal = false;
    public $editingId = null;

    // Form Fields
    public $asset_tag = '';
    public $name = '';
    public $category = 'Elektronik';
    public $serial_number = '';
    public $purchase_date = '';
    public $purchase_cost = '';
    public $status = 'available';
    public $condition = 'good';
    public $assigned_to = '';
    public $location = '';
    public $notes = '';

    // Import/Export Properties
    public bool $showImportModal = false;
    public bool $showErrorModal = false;
    public array $importErrors = [];
    public $excel_file = null;

    protected function rules()
    {
        return [
            'asset_tag' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'serial_number' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:available,assigned,maintenance,retired',
            'condition' => 'required|in:good,fair,damaged',
            'assigned_to' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
    }

    public function mount()
    {
        $this->purchase_date = date('Y-m-d');

        // Sinkronisasi notifikasi aset (rusak / dalam maintenance) saat halaman dibuka
        $this->syncAllAssetNotifications();
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    // =========================================================================
    // NOTIFIKASI ASET (RUSAK / MAINTENANCE)
    // =========================================================================

    /**
     * Jalankan pengecekan notifikasi untuk SEMUA aset.
     * Dipanggil saat halaman dimuat & setelah proses import,
     * karena kedua proses ini bisa memengaruhi banyak data sekaligus.
     */
    private function syncAllAssetNotifications(): void
    {
        Asset::all()->each(function (Asset $asset) {
            $this->syncAssetNotification($asset);
        });
    }

    /**
     * Sinkronisasi status notifikasi untuk SATU aset:
     * - Kirim notifikasi baru jika kondisi rusak / status maintenance dan belum ada notifikasi aktif.
     * - Tandai notifikasi lama sebagai dibaca jika kondisinya sudah kembali normal.
     */
    private function syncAssetNotification(Asset $asset): void
    {
        $asset->refresh();

        // --- Kondisi Rusak ---
        if ($asset->condition === 'damaged') {
            $this->fireAssetAlert(
                $asset,
                'asset_damaged',
                'Aset Rusak',
                "Aset '{$asset->name}' ({$asset->asset_tag}) dilaporkan dalam kondisi rusak. Segera tindak lanjuti perbaikan/penggantian."
            );
        } else {
            $this->clearAssetAlert($asset, 'asset_damaged');
        }

        // --- Status Maintenance ---
        if ($asset->status === 'maintenance') {
            $this->fireAssetAlert(
                $asset,
                'asset_maintenance',
                'Aset Dalam Maintenance',
                "Aset '{$asset->name}' ({$asset->asset_tag}) sedang berstatus maintenance/servis."
            );
        } else {
            $this->clearAssetAlert($asset, 'asset_maintenance');
        }
    }

    private function fireAssetAlert(Asset $asset, string $type, string $title, string $message): void
    {
        foreach (User::all() as $user) {
            $hasPending = $user->unreadNotifications()
                ->where('data->asset_id', $asset->id)
                ->where('data->asset_alert_type', $type)
                ->exists();

            if (!$hasPending) {
                $user->notify(new SystemNotification(
                    title: $title,
                    message: $message,
                    badge: $type === 'asset_damaged' ? 'Rusak' : 'Maintenance',
                    actionable: false,
                    url: url()->current(),
                    extraData: [
                        'asset_id'         => $asset->id,
                        'asset_alert_type' => $type,
                    ]
                ));
            }
        }
    }

    private function clearAssetAlert(Asset $asset, string $type): void
    {
        foreach (User::all() as $user) {
            $user->unreadNotifications()
                ->where('data->asset_id', $asset->id)
                ->where('data->asset_alert_type', $type)
                ->update(['read_at' => now()]);
        }
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getAssetsQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function bulkDelete()
    {
        $assets = Asset::whereIn('id', $this->selectedRows)->get(['id', 'asset_tag', 'name']);

        // Bersihkan notifikasi terkait aset yang akan dihapus
        foreach ($assets as $asset) {
            $this->clearAssetAlert($asset, 'asset_damaged');
            $this->clearAssetAlert($asset, 'asset_maintenance');
        }

        Asset::whereIn('id', $this->selectedRows)->delete();

        AuditLog::record(
            'ASSET_BULK_DELETE',
            null,
            count($assets) . ' aset dihapus: ' . $assets->pluck('asset_tag')->implode(', '),
            null,
            ['ids' => $assets->pluck('id')->all(), 'asset_tags' => $assets->pluck('asset_tag')->all()]
        );

        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Aset terpilih berhasil dihapus.');
    }

    public function bulkUpdateStatus($status)
    {
        if (!in_array($status, ['available', 'assigned', 'maintenance', 'retired'])) return;

        $assets = Asset::whereIn('id', $this->selectedRows)->get(['id', 'asset_tag', 'status']);

        Asset::whereIn('id', $this->selectedRows)->update(['status' => $status]);

        AuditLog::record(
            'ASSET_BULK_STATUS_UPDATE',
            null,
            count($assets) . " aset diubah statusnya menjadi '{$status}': " . $assets->pluck('asset_tag')->implode(', '),
            ['statuses' => $assets->pluck('status', 'asset_tag')->all()],
            ['status' => $status]
        );

        // Sinkronisasi notifikasi untuk setiap aset yang statusnya berubah
        foreach (Asset::whereIn('id', $this->selectedRows)->get() as $asset) {
            $this->syncAssetNotification($asset);
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Status aset terpilih berhasil diperbarui.');
    }

    public function openModal()
    {
        $this->resetForm();
        $this->asset_tag = 'AST-' . strtoupper(Str::random(6));
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['editingId', 'asset_tag', 'name', 'category', 'serial_number', 'purchase_cost', 'status', 'condition', 'assigned_to', 'location', 'notes']);
        $this->purchase_date = date('Y-m-d');
        $this->status = 'available';
        $this->condition = 'good';
        $this->category = 'Elektronik';
        $this->resetValidation();
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $this->editingId = $asset->id;
        $this->asset_tag = $asset->asset_tag;
        $this->name = $asset->name;
        $this->category = $asset->category;
        $this->serial_number = $asset->serial_number;
        $this->purchase_date = $asset->purchase_date ? date('Y-m-d', strtotime($asset->purchase_date)) : '';
        $this->purchase_cost = $asset->purchase_cost;
        $this->status = $asset->status;
        $this->condition = $asset->condition;
        $this->assigned_to = $asset->assigned_to;
        $this->location = $asset->location;
        $this->notes = $asset->notes;

        $this->resetValidation();
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();

        if ($this->editingId) {
            $asset = Asset::findOrFail($this->editingId);
            $oldValues = $asset->toArray();
            $asset->update($validated);

            AuditLog::record(
                'ASSET_UPDATE',
                $asset->asset_tag,
                "Aset '{$asset->name}' ({$asset->asset_tag}) diperbarui.",
                $oldValues,
                $asset->fresh()->toArray()
            );

            session()->flash('message', 'Data aset berhasil diperbarui.');
        } else {
            $asset = Asset::create($validated);

            AuditLog::record(
                'ASSET_CREATE',
                $asset->asset_tag,
                "Aset baru '{$asset->name}' ({$asset->asset_tag}) ditambahkan.",
                null,
                $asset->toArray()
            );

            session()->flash('message', 'Aset baru berhasil ditambahkan.');
        }

        // Sinkronisasi notifikasi kondisi & status aset
        $this->syncAssetNotification($asset);

        $this->closeModal();
    }

    public function delete($id)
    {
        $asset = Asset::findOrFail($id);
        $oldValues = $asset->toArray();

        // Bersihkan notifikasi terkait aset yang akan dihapus
        $this->clearAssetAlert($asset, 'asset_damaged');
        $this->clearAssetAlert($asset, 'asset_maintenance');

        $asset->delete();

        AuditLog::record(
            'ASSET_DELETE',
            $asset->asset_tag,
            "Aset '{$asset->name}' ({$asset->asset_tag}) dihapus.",
            $oldValues,
            null
        );

        session()->flash('message', 'Aset berhasil dihapus.');
    }

    private function getAssetsQuery()
    {
        return Asset::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('asset_tag', 'like', '%'.$this->search.'%')
                      ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                      ->orWhere('assigned_to', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn($q) => $q->where('category', $this->categoryFilter))
            ->latest();
    }

    // =========================================================================
    // EXCEL IMPORT & EXPORT
    // =========================================================================

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
        return Excel::download(new AssetTemplateExport, 'Template_Import_Aset.xlsx');
    }

    public function importExcel(): void
    {
        $this->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ], [], [
            'excel_file' => 'Berkas Excel'
        ]);

        $fileName = $this->excel_file->getClientOriginalName();
        $import = new AssetsImport();
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

            $this->showImportModal = false;
            $this->showErrorModal = true;
            return;
        }

        AuditLog::record(
            'ASSET_IMPORT',
            $fileName,
            "Data aset diimpor dari berkas '{$fileName}'.",
            null,
            null
        );

        // Sinkronisasi notifikasi karena import bisa mengubah banyak data sekaligus
        $this->syncAllAssetNotifications();

        $this->closeImportModal();
        session()->flash('message', 'Data aset dari Excel berhasil diimpor.');
    }

    public function exportData()
    {
        $filters = [
            'search'         => $this->search,
            'statusFilter'   => $this->statusFilter,
            'categoryFilter' => $this->categoryFilter,
        ];

        $fileName = 'Data_Aset_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            'ASSET_EXPORT',
            $fileName,
            'Data aset diekspor ke Excel.',
            null,
            $filters
        );

        return Excel::download(new AssetExport($filters), $fileName);
    }

    public function exportSelected()
    {
        if (empty($this->selectedRows)) {
            session()->flash('message', 'Pilih minimal satu aset terlebih dahulu untuk diekspor.');
            return;
        }

        $fileName = 'Data_Aset_Terpilih_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            'ASSET_EXPORT_SELECTED',
            $fileName,
            count($this->selectedRows) . ' aset terpilih diekspor ke Excel.',
            null,
            ['ids' => $this->selectedRows]
        );

        return Excel::download(new AssetExport([], $this->selectedRows), $fileName);
    }

    public function render()
    {
        return view('livewire.master.asset.index', [
            'assets' => $this->getAssetsQuery()->paginate($this->perPage),
            'totalAssets' => Asset::count(),
            'totalValue' => Asset::sum('purchase_cost'),
            'availableCount' => Asset::where('status', 'available')->count(),
            'assignedCount' => Asset::where('status', 'assigned')->count(),
            'maintenanceCount' => Asset::where('status', 'maintenance')->count(),
        ]);
    }
}