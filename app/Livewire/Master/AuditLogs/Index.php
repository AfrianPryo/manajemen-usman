<?php

namespace App\Livewire\Master\AuditLogs;

use App\Models\AuthLog;
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

    public function render()
    {
        // Audit log khusus untuk event penting (password change, reset, forbidden, dll)
        $criticalEvents = [
            'password.changed',
            'password.reset_by_admin',
            'access.forbidden',
            'login.failed',
        ];

        $logs = AuthLog::with('user')
            ->whereIn('event', $criticalEvents)
            ->when($this->search, fn ($q) => $q->where('identifier', 'like', "%{$this->search}%")
                                                ->orWhere('description', 'like', "%{$this->search}%"))
            ->latest('created_at')
            ->paginate(20);

        return view('livewire.master.audit-logs.index', [
            'logs' => $logs,
        ]);
    }
}