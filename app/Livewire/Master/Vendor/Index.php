<?php

namespace App\Livewire\Master\Vendor;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Vendor;
use App\Models\AuditLog;

class Index extends Component
{
    use WithPagination;

    // Filter & Search
    public $search = '';
    public $filterCategory = '';
    public $perPage = 10;

    // Bulk Action Selection
    public $selectedRows = [];
    public $selectAll = false;

    // Form Modal State
    public $isModalOpen = false;
    public $vendorId = null;

    // Form Fields
    public $name = '';
    public $category = 'perusahaan';
    public $contact_name = '';
    public $email = '';
    public $phone = '';
    public $website = '';
    public $address = '';
    public $id_number = '';
    public $contract_start_date = '';
    public $contract_end_date = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'required|in:perusahaan,pemerintah,individu,lainnya',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'website' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'id_number' => 'nullable|string|max:100',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after_or_equal:contract_start_date',
        ];
    }

    // Reset pagination ketika filter/search/perPage berubah
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterCategory() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    // Logic Select All Checkbox
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = $this->getVendorsQuery()
                ->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // Bulk Delete Action
    public function bulkDelete()
    {
        $vendors = Vendor::whereIn('id', $this->selectedRows)->get(['id', 'name']);

        Vendor::whereIn('id', $this->selectedRows)->delete();

        AuditLog::record(
            'VENDOR_BULK_DELETE',
            null,
            count($vendors) . ' vendor dihapus: ' . $vendors->pluck('name')->implode(', '),
            null,
            ['ids' => $vendors->pluck('id')->all(), 'names' => $vendors->pluck('name')->all()]
        );

        $this->selectedRows = [];
        $this->selectAll = false;

        session()->flash('message', 'Vendor terpilih berhasil dihapus.');
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset([
            'vendorId', 'name', 'contact_name', 'email', 'phone',
            'website', 'address', 'id_number',
            'contract_start_date', 'contract_end_date',
        ]);
        $this->category = 'perusahaan';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    public function edit($id)
    {
        $vendor = Vendor::findOrFail($id);
        $this->vendorId = $vendor->id;
        $this->name = $vendor->name;
        $this->category = $vendor->category ?? 'perusahaan';
        $this->contact_name = $vendor->contact_name;
        $this->email = $vendor->email;
        $this->phone = $vendor->phone;
        $this->website = $vendor->website;
        $this->address = $vendor->address;
        $this->id_number = $vendor->id_number ?? '';
        $this->contract_start_date = $vendor->contract_start_date
            ? $vendor->contract_start_date->format('Y-m-d')
            : '';
        $this->contract_end_date = $vendor->contract_end_date
            ? $vendor->contract_end_date->format('Y-m-d')
            : '';

        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function save()
    {
        $this->validate();

        $isUpdate = (bool) $this->vendorId;
        $oldValues = $isUpdate ? Vendor::find($this->vendorId)?->toArray() : null;

        $vendor = Vendor::updateOrCreate(
            ['id' => $this->vendorId],
            [
                'name' => $this->name,
                'category' => $this->category,
                'contact_name' => $this->contact_name,
                'email' => $this->email,
                'phone' => $this->phone,
                'website' => $this->website,
                'address' => $this->address,
                'id_number' => $this->id_number,
                'contract_start_date' => $this->contract_start_date ?: null,
                'contract_end_date' => $this->contract_end_date ?: null,
            ]
        );

        AuditLog::record(
            $isUpdate ? 'VENDOR_UPDATE' : 'VENDOR_CREATE',
            $vendor->name,
            $isUpdate
                ? "Vendor '{$vendor->name}' diperbarui."
                : "Vendor baru '{$vendor->name}' ditambahkan.",
            $oldValues,
            $vendor->toArray()
        );

        session()->flash('message', $this->vendorId ? 'Vendor berhasil diperbarui.' : 'Vendor berhasil ditambahkan.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $vendor = Vendor::findOrFail($id);
        $oldValues = $vendor->toArray();
        $vendor->delete();

        AuditLog::record(
            'VENDOR_DELETE',
            $vendor->name,
            "Vendor '{$vendor->name}' dihapus.",
            $oldValues,
            null
        );

        session()->flash('message', 'Vendor berhasil dihapus.');
    }

    private function getVendorsQuery()
    {
        return Vendor::query()
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('contact_name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%')
                      ->orWhere('phone', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->filterCategory, function ($query) {
                $query->where('category', $this->filterCategory);
            })
            ->latest();
    }

    public function render()
    {
        return view('livewire.master.vendor.index', [
            'vendors' => $this->getVendorsQuery()->paginate($this->perPage),
            'totalVendors' => Vendor::count(),
        ])->layout('components.layouts.app', ['title' => 'Vendors']);
    }
}