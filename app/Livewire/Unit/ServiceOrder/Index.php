<?php

namespace App\Livewire\Unit\ServiceOrder;

use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\AuditLog;
use App\Models\ServiceOrder;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modul "Pesanan Layanan" -- HANYA relevan untuk unit usaha berkategori
 * 'jasa' (route-nya sudah dijaga middleware 'unit.category:jasa' di
 * routes/web.php, lihat komentar di sana). Ditulis berdiri sendiri
 * (bukan extends komponen Master, karena memang tidak ada modul serupa
 * di sisi Master) tapi memakai konvensi yang identik dengan modul unit
 * lain: WithPagination, trait ScopedToUnit untuk penguncian unit_id, pola
 * modal Tambah/Edit, dan pencatatan AuditLog::record() di setiap aksi
 * tulis -- persis seperti Unit\Inventory\Index & Master\Vendor\Index.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Pesanan Layanan')]
class Index extends Component
{
    use WithPagination, ScopedToUnit;

    public string $search = '';
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $serviceOrderId = null;

    // Form Inputs
    public string $customer_name = '';
    public string $customer_phone = '';
    public string $service_name = '';
    public string $description = '';
    public string $assigned_to = '';
    public string $price = '';
    public string $scheduled_at = '';
    public string $status = 'pending';
    public string $notes = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'customer_name'  => 'required|string|max:150',
            'customer_phone' => 'nullable|string|max:30',
            'service_name'   => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'assigned_to'    => 'nullable|string|max:100',
            'price'          => 'required|numeric|min:0',
            'scheduled_at'   => 'nullable|date',
            'status'         => 'required|in:pending,in_progress,completed,cancelled',
            'notes'          => 'nullable|string|max:1000',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'serviceOrderId', 'isEditing', 'customer_name', 'customer_phone',
            'service_name', 'description', 'assigned_to', 'price',
            'scheduled_at', 'notes',
        ]);
        $this->status = 'pending';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();

        $order = ServiceOrder::where('unit_id', $this->currentUnitId())->findOrFail($id);

        $this->serviceOrderId  = $order->id;
        $this->customer_name   = $order->customer_name;
        $this->customer_phone  = $order->customer_phone ?? '';
        $this->service_name    = $order->service_name;
        $this->description     = $order->description ?? '';
        $this->assigned_to     = $order->assigned_to ?? '';
        $this->price           = (string) $order->price;
        $this->scheduled_at    = optional($order->scheduled_at)->format('Y-m-d\TH:i') ?? '';
        $this->status          = $order->status;
        $this->notes           = $order->notes ?? '';

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
            'unit_id'        => $this->currentUnitId(),
            'customer_name'  => $this->customer_name,
            'customer_phone' => $this->customer_phone ?: null,
            'service_name'   => $this->service_name,
            'description'    => $this->description ?: null,
            'assigned_to'    => $this->assigned_to ?: null,
            'price'          => $this->price,
            'scheduled_at'   => $this->scheduled_at ?: null,
            'status'         => $this->status,
            'notes'          => $this->notes ?: null,
        ];

        if ($this->isEditing && $this->serviceOrderId) {
            $order = ServiceOrder::where('unit_id', $this->currentUnitId())->findOrFail($this->serviceOrderId);
            $oldValues = $order->getAttributes();

            $order->update($data);

            AuditLog::record(
                event: 'SERVICE_ORDER_UPDATED',
                identifier: $order->service_name,
                description: "Admin unit memperbarui pesanan layanan '{$order->service_name}' milik {$order->customer_name}",
                oldValues: $oldValues,
                newValues: $order->getAttributes()
            );

            session()->flash('message', 'Pesanan layanan berhasil diperbarui.');
        } else {
            $data['user_id'] = auth()->id();
            $order = ServiceOrder::create($data);

            AuditLog::record(
                event: 'SERVICE_ORDER_CREATED',
                identifier: $order->service_name,
                description: "Admin unit menambahkan pesanan layanan baru '{$order->service_name}' untuk {$order->customer_name}",
                oldValues: null,
                newValues: $order->getAttributes()
            );

            session()->flash('message', 'Pesanan layanan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Aksi cepat ubah status langsung dari tabel (tanpa buka modal), umum
     * dipakai untuk alur kerja jasa: pending -> in_progress -> completed.
     */
    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'], true)) {
            return;
        }

        $order = ServiceOrder::where('unit_id', $this->currentUnitId())->findOrFail($id);
        $oldStatus = $order->status;

        $order->status = $status;
        $order->save();

        AuditLog::record(
            event: 'SERVICE_ORDER_STATUS_UPDATED',
            identifier: $order->service_name,
            description: "Status pesanan layanan '{$order->service_name}' diubah dari {$oldStatus} menjadi {$status}",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status]
        );

        session()->flash('message', 'Status pesanan layanan berhasil diperbarui.');
    }

    public function deleteServiceOrder(int $id): void
    {
        $order = ServiceOrder::where('unit_id', $this->currentUnitId())->findOrFail($id);

        $identifier = $order->service_name;
        $oldValues = $order->getAttributes();

        $order->delete();

        AuditLog::record(
            event: 'SERVICE_ORDER_DELETED',
            identifier: $identifier,
            description: "Admin unit menghapus pesanan layanan '{$identifier}'",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('message', 'Pesanan layanan berhasil dihapus.');
    }

    public function render()
    {
        $unitId = $this->currentUnitId();

        $orders = ServiceOrder::query()
            ->where('unit_id', $unitId)
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('customer_name', 'like', '%' . $this->search . '%')
                        ->orWhere('service_name', 'like', '%' . $this->search . '%')
                        ->orWhere('assigned_to', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->latest('scheduled_at')
            ->latest('id')
            ->paginate(10);

        return view('livewire.unit.service-order.index', [
            'orders'          => $orders,
            'totalOrders'     => ServiceOrder::where('unit_id', $unitId)->count(),
            'pendingCount'    => ServiceOrder::where('unit_id', $unitId)->where('status', 'pending')->count(),
            'inProgressCount' => ServiceOrder::where('unit_id', $unitId)->where('status', 'in_progress')->count(),
            'completedCount'  => ServiceOrder::where('unit_id', $unitId)->where('status', 'completed')->count(),
        ]);
    }
}
