<?php

namespace App\Livewire\Master\Asset;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use App\Models\Asset;
use App\Exports\AssetTemplateExport;
use App\Exports\AssetExport;
use App\Imports\AssetsImport;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
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
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingStatusFilter() { $this->resetPage(); }
    public function updatingCategoryFilter() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

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
        Asset::whereIn('id', $this->selectedRows)->delete();
        $this->selectedRows = [];
        $this->selectAll = false;
        session()->flash('message', 'Aset terpilih berhasil dihapus.');
    }

    public function bulkUpdateStatus($status)
    {
        if (!in_array($status, ['available', 'assigned', 'maintenance', 'retired'])) return;

        Asset::whereIn('id', $this->selectedRows)->update(['status' => $status]);
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
            $asset->update($validated);
            session()->flash('message', 'Data aset berhasil diperbarui.');
        } else {
            Asset::create($validated);
            session()->flash('message', 'Aset baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        Asset::findOrFail($id)->delete();
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

        return Excel::download(new AssetExport($filters), $fileName);
    }

    public function exportSelected()
    {
        if (empty($this->selectedRows)) {
            session()->flash('message', 'Pilih minimal satu aset terlebih dahulu untuk diekspor.');
            return;
        }

        $fileName = 'Data_Aset_Terpilih_' . now()->format('Ymd_His') . '.xlsx';

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