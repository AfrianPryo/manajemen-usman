<?php

namespace App\Livewire\Master\Customers;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modul "Manajemen Pelanggan" versi Master Admin -- pasangan lintas-unit
 * dari App\Livewire\Unit\Customers\Index, ditulis sejajar (mirror) dengan
 * pola Master\ServiceOrder\Index: data ditampilkan gabungan dari SEMUA
 * Unit Usaha sekaligus, lengkap dengan dropdown filter & field pemilihan
 * Unit Usaha di form.
 *
 * BERBEDA dengan Master\ServiceOrder\Index: modul ini TIDAK dibatasi ke
 * unit berkategori 'jasa' -- Manajemen Pelanggan berlaku untuk SEMUA
 * kategori Unit Usaha (ritel maupun jasa), sehingga jasaUnits() di sana
 * diganti allUnits() di sini (tanpa filter category sama sekali) dan
 * query index tidak memakai whereHas('unit', category = 'jasa').
 */
#[Layout('components.layouts.app')]
#[Title('Manajemen Pelanggan')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = '';
    public string $unitFilter = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $customerId = null;

    // Form Inputs
    public string $unit_id = '';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $gender = '';
    public string $birth_date = '';
    public string $category = 'baru';
    public string $address = '';
    public string $notes = '';
    public bool $is_active = true;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingCategoryFilter(): void { $this->resetPage(); }
    public function updatingUnitFilter(): void { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'unit_id'    => 'required|exists:units,id',
            'name'       => 'required|string|max:150',
            'phone'      => 'nullable|string|max:30',
            'email'      => 'nullable|email|max:255',
            'gender'     => 'nullable|in:L,P',
            'birth_date' => 'nullable|date',
            'category'   => 'required|in:baru,reguler,member,vip',
            'address'    => 'nullable|string|max:1000',
            'notes'      => 'nullable|string|max:1000',
        ];
    }

    /**
     * Unit Usaha yang boleh dipilih di form -- SEMUA unit, tanpa filter
     * kategori (lihat catatan class di atas).
     */
    private function allUnits()
    {
        return Unit::orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'customerId', 'isEditing', 'unit_id', 'name', 'phone', 'email',
            'gender', 'birth_date', 'address', 'notes',
        ]);
        $this->category = 'baru';
        $this->is_active = true;
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();

        $customer = Customer::findOrFail($id);

        $this->customerId  = $customer->id;
        $this->unit_id     = (string) $customer->unit_id;
        $this->name        = $customer->name;
        $this->phone       = $customer->phone ?? '';
        $this->email       = $customer->email ?? '';
        $this->gender      = $customer->gender ?? '';
        $this->birth_date  = optional($customer->birth_date)->format('Y-m-d') ?? '';
        $this->category    = $customer->category;
        $this->address     = $customer->address ?? '';
        $this->notes       = $customer->notes ?? '';
        $this->is_active   = (bool) $customer->is_active;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'unit_id'    => $this->unit_id,
            'name'       => $this->name,
            'phone'      => $this->phone ?: null,
            'email'      => $this->email ?: null,
            'gender'     => $this->gender ?: null,
            'birth_date' => $this->birth_date ?: null,
            'category'   => $this->category,
            'address'    => $this->address ?: null,
            'notes'      => $this->notes ?: null,
            'is_active'  => $this->is_active,
        ];

        if ($this->isEditing && $this->customerId) {
            $customer = Customer::findOrFail($this->customerId);
            $oldValues = $customer->getAttributes();

            $customer->update($data);

            AuditLog::record(
                event: 'CUSTOMER_UPDATED',
                identifier: $customer->name,
                description: "Admin master memperbarui data pelanggan '{$customer->name}' (Unit: {$customer->unit?->name})",
                oldValues: $oldValues,
                newValues: $customer->getAttributes()
            );

            session()->flash('message', 'Data pelanggan berhasil diperbarui.');
        } else {
            $data['user_id'] = auth()->id();
            $customer = Customer::create($data);

            AuditLog::record(
                event: 'CUSTOMER_CREATED',
                identifier: $customer->name,
                description: "Admin master menambahkan pelanggan baru '{$customer->name}' (Unit: {$customer->unit?->name})",
                oldValues: null,
                newValues: $customer->getAttributes()
            );

            session()->flash('message', 'Pelanggan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Aksi cepat "Catat Kunjungan" langsung dari tabel (tanpa buka modal),
     * mirip pola updateStatus() di Master\ServiceOrder\Index.
     */
    public function recordVisit(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->recordVisit();

        AuditLog::record(
            event: 'CUSTOMER_VISIT_RECORDED',
            identifier: $customer->name,
            description: "Admin master mencatat kunjungan baru untuk pelanggan '{$customer->name}' (Unit: {$customer->unit?->name}, total kunjungan: {$customer->total_visits})",
            oldValues: null,
            newValues: ['total_visits' => $customer->total_visits, 'last_visit_at' => (string) $customer->last_visit_at]
        );

        session()->flash('message', 'Kunjungan pelanggan berhasil dicatat.');
    }

    public function deleteCustomer(int $id): void
    {
        $customer = Customer::findOrFail($id);

        $identifier = $customer->name;
        $unitName = $customer->unit?->name;
        $oldValues = $customer->getAttributes();

        $customer->delete();

        AuditLog::record(
            event: 'CUSTOMER_DELETED',
            identifier: $identifier,
            description: "Admin master menghapus data pelanggan '{$identifier}' (Unit: {$unitName})",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('message', 'Data pelanggan berhasil dihapus.');
    }

    private function getFilteredCustomersQuery()
    {
        return Customer::query()
            ->with('unit')
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category', $this->categoryFilter);
            })
            ->when($this->unitFilter, function ($query) {
                $query->where('unit_id', $this->unitFilter);
            });
    }

    public function render()
    {
        $baseQuery = Customer::query();

        $customers = $this->getFilteredCustomersQuery()
            ->latest('id')
            ->paginate(10);

        return view('livewire.master.customers.index', [
            'customers'      => $customers,
            'units'          => $this->allUnits(),
            'totalCustomers' => (clone $baseQuery)->count(),
            'vipCount'       => (clone $baseQuery)->where('category', 'vip')->count(),
            'memberCount'    => (clone $baseQuery)->where('category', 'member')->count(),
            'newCount'       => (clone $baseQuery)->where('category', 'baru')->count(),
        ]);
    }
}
