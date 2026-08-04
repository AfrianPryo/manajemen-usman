<?php

namespace App\Livewire\Unit;

use App\Models\Unit; // Pastikan ini sesuai dengan nama model Anda (Unit atau UnitUsaha)
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard Unit')]
class Dashboard extends Component
{
    // 🔴 Type-hint Model Unit agar Livewire otomatis resolve dari database
    public Unit $unit;

    public function mount(Unit $unit)
    {
        $this->unit = $unit;
    }

    public function render()
    {
        return view('livewire.unit.dashboard');
    }
}