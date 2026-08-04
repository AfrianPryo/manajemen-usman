<?php

namespace App\Livewire\Master\Reports;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Laporan Konsolidasi')]
class Index extends Component
{
    public string $period = 'monthly'; // daily, weekly, monthly, yearly
    public string $startDate = '';
    public string $endDate = '';

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.master.reports.index');
    }
}