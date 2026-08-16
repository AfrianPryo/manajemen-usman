<?php

namespace App\Livewire\Master\AuditLog;

use App\Models\AuditLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Audit Log Sistem')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';
    public int $perPage = 15;

    public bool $showDetailModal = false;
    public ?AuditLog $selectedLog = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }

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