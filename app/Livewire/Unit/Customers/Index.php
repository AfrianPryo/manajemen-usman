<?php

namespace App\Livewire\Unit\Customers;

use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\AuditLog;
use App\Models\Customer;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modul "Manajemen Pelanggan" -- BERBEDA dengan Unit\ServiceOrder\Index,
 * modul ini berlaku untuk SEMUA kategori Unit Usaha (ritel maupun jasa),
 * jadi TIDAK ada middleware 'unit.category:...' yang memagari route-nya
 * (lihat routes/web.php) dan TIDAK ada key 'unit_category' di
 * config/menu.php untuk item menunya -- selalu tampil di kedua kategori.
 *
 * Selebihnya ditulis dengan konvensi yang identik dengan modul unit lain:
 * WithPagination, trait ScopedToUnit untuk penguncian unit_id, pola modal
 * Tambah/Edit, dan pencatatan AuditLog::record() di setiap aksi tulis --
 * persis seperti Unit\ServiceOrder\Index & Unit\Inventory\Index.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Manajemen Pelanggan')]
class Index extends Component
{
    use WithPagination, ScopedToUnit;

    public string $search = '';
    public string $categoryFilter = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $customerId = null;

    // Form Inputs
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

    protected function rules(): array
    {
        return [
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

    public function openCreateModal(): void
    {
        $this->reset([
            'customerId', 'isEditing', 'name', 'phone', 'email',
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

        $customer = Customer::where('unit_id', $this->currentUnitId())->findOrFail($id);

        $this->customerId  = $customer->id;
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
            'unit_id'    => $this->currentUnitId(),
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
            $customer = Customer::where('unit_id', $this->currentUnitId())->findOrFail($this->customerId);
            $oldValues = $customer->getAttributes();

            $customer->update($data);

            AuditLog::record(
                event: 'CUSTOMER_UPDATED',
                identifier: $customer->name,
                description: "Admin unit memperbarui data pelanggan '{$customer->name}'",
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
                description: "Admin unit menambahkan pelanggan baru '{$customer->name}'",
                oldValues: null,
                newValues: $customer->getAttributes()
            );

            session()->flash('message', 'Pelanggan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Aksi cepat "Catat Kunjungan" langsung dari tabel (tanpa buka modal),
     * mirip pola updateStatus() di Unit\ServiceOrder\Index.
     */
    public function recordVisit(int $id): void
    {
        $customer = Customer::where('unit_id', $this->currentUnitId())->findOrFail($id);
        $customer->recordVisit();

        AuditLog::record(
            event: 'CUSTOMER_VISIT_RECORDED',
            identifier: $customer->name,
            description: "Admin unit mencatat kunjungan baru untuk pelanggan '{$customer->name}' (total kunjungan: {$customer->total_visits})",
            oldValues: null,
            newValues: ['total_visits' => $customer->total_visits, 'last_visit_at' => (string) $customer->last_visit_at]
        );

        session()->flash('message', 'Kunjungan pelanggan berhasil dicatat.');
    }

    public function deleteCustomer(int $id): void
    {
        $customer = Customer::where('unit_id', $this->currentUnitId())->findOrFail($id);

        $identifier = $customer->name;
        $oldValues = $customer->getAttributes();

        $customer->delete();

        AuditLog::record(
            event: 'CUSTOMER_DELETED',
            identifier: $identifier,
            description: "Admin unit menghapus data pelanggan '{$identifier}'",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('message', 'Data pelanggan berhasil dihapus.');
    }

    public function render()
    {
        $unitId = $this->currentUnitId();

        $customers = Customer::query()
            ->where('unit_id', $unitId)
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
            ->latest('id')
            ->paginate(10);

        return view('livewire.unit.customers.index', [
            'customers'      => $customers,
            'totalCustomers' => Customer::where('unit_id', $unitId)->count(),
            'vipCount'       => Customer::where('unit_id', $unitId)->where('category', 'vip')->count(),
            'memberCount'    => Customer::where('unit_id', $unitId)->where('category', 'member')->count(),
            'newCount'       => Customer::where('unit_id', $unitId)->where('category', 'baru')->count(),
        ]);
    }
}
