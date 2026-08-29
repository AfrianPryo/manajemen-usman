<?php

namespace App\Livewire\Master\ServiceOrder;

use App\Models\AuditLog;
use App\Models\ServiceOrder;
use App\Models\Unit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Modul "Pesanan Layanan" versi Master Admin -- pasangan lintas-unit dari
 * App\Livewire\Unit\ServiceOrder\Index. Fitur ini tetap HANYA relevan untuk
 * unit usaha berkategori 'jasa' (lihat Unit::$category), tapi di sisi Master
 * datanya ditampilkan gabungan dari SEMUA unit berkategori 'jasa' sekaligus
 * (bukan dikunci ke satu unit seperti trait ScopedToUnit di sisi Unit),
 * lengkap dengan dropdown filter & field pemilihan Unit Usaha di form --
 * persis pola yang sama dipakai Master\RecurringTransaction\Index untuk
 * relasi data unit_id lintas-unit.
 *
 * Method, validasi, dan alur modal Tambah/Edit/Ubah Status/Hapus sengaja
 * ditulis sejajar (mirror) dengan Unit\ServiceOrder\Index supaya kedua
 * modul mudah dirawat berdampingan; perbedaan utama hanya pada scope query
 * (lintas unit vs satu unit) dan field unit_id tambahan pada form.
 */
#[Layout('components.layouts.app')]
#[Title('Pesanan Layanan')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $unitFilter = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $serviceOrderId = null;

    // Form Inputs
    public string $unit_id = '';
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
    public function updatingUnitFilter(): void { $this->resetPage(); }

    protected function rules(): array
    {
        return [
            'unit_id'        => 'required|exists:units,id',
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

    /**
     * Unit Usaha yang boleh dipilih di form -- dibatasi hanya kategori
     * 'jasa', karena fitur Pesanan Layanan memang tidak berlaku untuk unit
     * berkategori 'ritel' (lihat juga middleware 'unit.category:jasa' di
     * routes/web.php untuk sisi Unit Admin).
     */
    private function jasaUnits()
    {
        return Unit::where('category', 'jasa')->orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'serviceOrderId', 'isEditing', 'unit_id', 'customer_name', 'customer_phone',
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

        $order = ServiceOrder::findOrFail($id);

        $this->serviceOrderId  = $order->id;
        $this->unit_id         = (string) $order->unit_id;
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
            'unit_id'        => $this->unit_id,
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
            $order = ServiceOrder::findOrFail($this->serviceOrderId);
            $oldValues = $order->getAttributes();

            $order->update($data);

            AuditLog::record(
                event: 'SERVICE_ORDER_UPDATED',
                identifier: $order->service_name,
                description: "Admin master memperbarui pesanan layanan '{$order->service_name}' milik {$order->customer_name} (Unit: {$order->unit?->name})",
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
                description: "Admin master menambahkan pesanan layanan baru '{$order->service_name}' untuk {$order->customer_name} (Unit: {$order->unit?->name})",
                oldValues: null,
                newValues: $order->getAttributes()
            );

            session()->flash('message', 'Pesanan layanan berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    /**
     * Aksi cepat ubah status langsung dari tabel (tanpa buka modal), sama
     * seperti di sisi Unit: pending -> in_progress -> completed.
     */
    public function updateStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'], true)) {
            return;
        }

        $order = ServiceOrder::findOrFail($id);
        $oldStatus = $order->status;

        $order->status = $status;
        $order->save();

        AuditLog::record(
            event: 'SERVICE_ORDER_STATUS_UPDATED',
            identifier: $order->service_name,
            description: "Admin master mengubah status pesanan layanan '{$order->service_name}' (Unit: {$order->unit?->name}) dari {$oldStatus} menjadi {$status}",
            oldValues: ['status' => $oldStatus],
            newValues: ['status' => $status]
        );

        session()->flash('message', 'Status pesanan layanan berhasil diperbarui.');
    }

    public function deleteServiceOrder(int $id): void
    {
        $order = ServiceOrder::findOrFail($id);

        $identifier = $order->service_name;
        $unitName = $order->unit?->name;
        $oldValues = $order->getAttributes();

        $order->delete();

        AuditLog::record(
            event: 'SERVICE_ORDER_DELETED',
            identifier: $identifier,
            description: "Admin master menghapus pesanan layanan '{$identifier}' (Unit: {$unitName})",
            oldValues: $oldValues,
            newValues: null
        );

        session()->flash('message', 'Pesanan layanan berhasil dihapus.');
    }

    private function getFilteredOrdersQuery()
    {
        return ServiceOrder::query()
            ->with('unit')
            ->whereHas('unit', fn ($q) => $q->where('category', 'jasa'))
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
            ->when($this->unitFilter, function ($query) {
                $query->where('unit_id', $this->unitFilter);
            });
    }

    public function render()
    {
        $baseQuery = ServiceOrder::query()->whereHas('unit', fn ($q) => $q->where('category', 'jasa'));

        $orders = $this->getFilteredOrdersQuery()
            ->latest('scheduled_at')
            ->latest('id')
            ->paginate(10);

        return view('livewire.master.service-order.index', [
            'orders'          => $orders,
            'units'           => $this->jasaUnits(),
            'totalOrders'     => (clone $baseQuery)->count(),
            'pendingCount'    => (clone $baseQuery)->where('status', 'pending')->count(),
            'inProgressCount' => (clone $baseQuery)->where('status', 'in_progress')->count(),
            'completedCount'  => (clone $baseQuery)->where('status', 'completed')->count(),
        ]);
    }
}
