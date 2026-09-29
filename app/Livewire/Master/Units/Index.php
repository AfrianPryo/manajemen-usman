<?php

namespace App\Livewire\Master\Units;

use App\Models\AuditLog;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Unit Usaha')]
class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public string $search = '';
    public string $departmentFilter = '';
    public string $categoryFilter = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $unitId = null;

    // Form Inputs
    public string $name = '';
    public string $department = 'PPLG';
    public string $category = 'ritel';
    public string $pic_name = '';
    public string $phone = '';
    public string $description = '';
    public bool $is_active = true;

    // Logo Unit Usaha (opsional)
    public $logo = null;
    public ?string $existingLogo = null;
    public bool $removeLogo = false;

    // Auto Reset Page saat filter berubah
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingDepartmentFilter(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    public function openCreateModal(): void
    {
        $this->reset(['name', 'pic_name', 'phone', 'description', 'unitId', 'isEditing', 'logo', 'existingLogo', 'removeLogo']);
        $this->department = 'PPLG';
        $this->category = 'ritel';
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $unit = Unit::findOrFail($id);
        
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->department = $unit->department ?? 'PPLG';
        $this->category = $unit->category ?? 'ritel';
        $this->pic_name = $unit->pic_name ?? '';
        $this->phone = $unit->phone ?? '';
        $this->description = $unit->description ?? '';
        $this->is_active = (bool) $unit->is_active;
        $this->logo = null;
        $this->existingLogo = $unit->logo ?? null;
        $this->removeLogo = false;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * Tandai logo unit yang sudah tersimpan untuk dihapus saat form disimpan.
     * Hanya menghapus preview & flag di form; file baru benar-benar
     * dihapus dari storage pada method save().
     */
    public function clearLogo(): void
    {
        $this->logo = null;
        $this->existingLogo = null;
        $this->removeLogo = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => 'required|string|max:100|unique:units,name,' . $this->unitId,
            'department' => 'required|string|max:50',
            'category' => 'required|in:ritel,jasa',
            'pic_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'logo' => 'nullable|image|max:1024',
        ];

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'department' => $this->department,
            'category' => $this->category,
            'pic_name' => $this->pic_name ?: null,
            'phone' => $this->phone ?: null,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ];

        $previousLogo = $this->isEditing && $this->unitId
            ? optional(Unit::find($this->unitId))->logo
            : null;

        if ($this->logo) {
            // Logo baru diunggah: simpan file baru & tandai yang lama untuk dihapus
            $data['logo'] = $this->logo->store('unit-logos', 'public');
        } elseif ($this->removeLogo) {
            // Admin memilih menghapus logo tanpa menggantinya
            $data['logo'] = null;
        }

        if ($this->isEditing && $this->unitId) {
            $unit = Unit::findOrFail($this->unitId);
            $oldValues = $unit->getAttributes();

            $unit->update($data);

            // Audit Log: Edit Unit Usaha
            AuditLog::record(
                event: 'UNIT_UPDATED',
                identifier: $unit->name,
                description: "Admin memperbarui data unit usaha: {$unit->name}",
                oldValues: $oldValues,
                newValues: $unit->getAttributes()
            );

            session()->flash('message', 'Unit usaha berhasil diperbarui.');
        } else {
            $unit = Unit::create($data);

            // Audit Log: Tambah Unit Usaha Baru
            AuditLog::record(
                event: 'UNIT_CREATED',
                identifier: $unit->name,
                description: "Admin menambahkan unit usaha baru: {$unit->name}",
                oldValues: null,
                newValues: $unit->getAttributes()
            );

            session()->flash('message', 'Unit usaha berhasil ditambahkan.');
        }

        // Bersihkan file logo lama dari storage jika sudah diganti/dihapus
        if ($previousLogo && $previousLogo !== ($data['logo'] ?? $previousLogo)) {
            Storage::disk('public')->delete($previousLogo);
        }

        $this->closeModal();
    }

    public function toggleUnitStatus(int $id): void
    {
        $unit = Unit::findOrFail($id);
        $oldStatus = $unit->is_active;

        $unit->is_active = !$unit->is_active;
        $unit->save();

        $statusText = $unit->is_active ? 'Aktif' : 'Nonaktif';

        // Audit Log: Ubah Status Unit Usaha
        AuditLog::record(
            event: 'UNIT_STATUS_UPDATED',
            identifier: $unit->name,
            description: "Admin mengubah status unit usaha '{$unit->name}' menjadi {$statusText}",
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $unit->is_active]
        );

        session()->flash('message', "Status unit usaha berhasil diubah menjadi {$statusText}.");
    }

    /**
     * Menghapus unit usaha (Hanya jika berstatus Nonaktif).
     */
    public function deleteUnit(int $id): void
    {
        $unit = Unit::findOrFail($id);

        // Keamanan Sisi Server: Cegah penghapusan jika status unit masih AKTIF
        if ($unit->is_active) {
            session()->flash('error', 'Unit usaha hanya dapat dihapus jika statusnya NONAKTIF.');
            return;
        }

        // Keamanan Data: relasi FK ke unit disetel cascadeOnDelete, jadi menghapus
        // unit akan ikut memusnahkan seluruh riwayat keuangannya. Blokir kalau
        // unit masih punya data yang bernilai pembukuan/audit.
        // Daftar ini mengikuti seluruh tabel yang FK-nya cascadeOnDelete ke units
        // (finance_transactions, purchase_orders, products, customers, service_orders).
        // Aset, dokumen resmi, & user memakai nullOnDelete sehingga tidak ikut terhapus.
        $blockers = [];
        $checks = [
            'transaksi keuangan' => \App\Models\FinanceTransaction::class,
            'pembelian'          => \App\Models\PurchaseOrder::class,
            'produk'             => \App\Models\Product::class,
            'pelanggan'          => \App\Models\Customer::class,
            'order jasa'         => \App\Models\ServiceOrder::class,
        ];
        foreach ($checks as $label => $model) {
            if ($model::where('unit_id', $unit->id)->exists()) {
                $blockers[] = $label;
            }
        }

        if ($blockers) {
            session()->flash('error', 'Unit usaha tidak dapat dihapus karena masih memiliki data ' . implode(', ', $blockers) . '. Biarkan unit dalam status NONAKTIF agar riwayat pembukuan tetap terjaga.');
            return;
        }

        $unitName = $unit->name;
        $unitLogo = $unit->logo;
        $oldValues = $unit->getAttributes();

        // Lepas relasi admin/user berdasarkan jenis relasinya
        if (method_exists($unit, 'users')) {
            $relation = $unit->users();

            if ($relation instanceof BelongsToMany) {
                // Jika menggunakan tabel pivot (Many-to-Many)
                $relation->detach();
            } elseif ($relation instanceof HasMany) {
                // Jika relasi HasMany (misal: kolom unit_id di tabel users)
                $relation->update(['unit_id' => null]);
            }
        }

        // Hapus unit usaha
        if (method_exists($unit, 'forceDelete')) {
            $unit->forceDelete();
        } else {
            $unit->delete();
        }

        // Bersihkan file logo dari storage agar tidak jadi sampah
        if ($unitLogo) {
            Storage::disk('public')->delete($unitLogo);
        }

        // Audit Log: Hapus Unit Usaha
        AuditLog::record(
            event: 'UNIT_DELETED',
            identifier: $unitName,
            description: "Admin menghapus unit usaha: {$unitName}",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('message', 'Unit usaha berhasil dihapus secara permanen.');
    }

    public function render()
    {
        $units = Unit::query()
            ->with(['users' => function ($query) {
                $query->where('is_active', true);
            }])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('pic_name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->departmentFilter, function ($query) {
                $query->where('department', $this->departmentFilter);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('is_active', $this->statusFilter === '1');
            })
            ->latest()
            ->paginate(9);

        return view('livewire.master.units.index', [
            'units' => $units,
            'totalUnits' => Unit::count(),
            'activeUnits' => Unit::where('is_active', true)->count(),
            'inactiveUnits' => Unit::where('is_active', false)->count(),
        ]);
    }
}