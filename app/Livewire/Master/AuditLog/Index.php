<?php

namespace App\Livewire\Master\AuditLog;

use App\Exports\AuditLogExport;
use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.app')]
#[Title('Audit Log Sistem')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';
    public string $startDate = '';
    public string $endDate = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?AuditLog $selectedLog = null;

    // Reset halaman otomatis ketika filter berubah
    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }
    public function updatingStartDate(): void { $this->resetPage(); }
    public function updatingEndDate(): void { $this->resetPage(); }

    /**
     * Reset seluruh filter ke kondisi awal.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'eventFilter', 'startDate', 'endDate']);
        $this->resetPage();
    }

    public function openDetail(int $id): void
    {
        $this->selectedLog = AuditLog::with('user')->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->selectedLog = null;
    }

    /**
     * Export audit log sistem (mengikuti filter yang sedang aktif: search, event, rentang tanggal).
     */
    public function exportLog()
    {
        return Excel::download(
            new AuditLogExport([
                'search'      => $this->search,
                'eventFilter' => $this->eventFilter,
                'startDate'   => $this->startDate,
                'endDate'     => $this->endDate,
            ]),
            'audit-log-sistem-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->whereNotIn('event', ['USER_LOGIN', 'USER_LOGOUT', 'LOGIN_FAILED'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('identifier', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%")
                      ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
            ->when($this->startDate, fn ($q) => $q->whereDate('created_at', '>=', $this->startDate))
            ->when($this->endDate, fn ($q) => $q->whereDate('created_at', '<=', $this->endDate))
            ->latest()
            ->paginate($this->perPage);

        $events = AuditLog::whereNotIn('event', ['USER_LOGIN', 'USER_LOGOUT', 'LOGIN_FAILED'])
            ->distinct()
            ->pluck('event');

        return view('livewire.master.audit-log.index', [
            'logs' => $logs,
            'events' => $events,
        ]);
    }
}