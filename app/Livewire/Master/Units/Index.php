<?php

namespace App\Livewire\Master\Units;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Manajemen Unit Usaha')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.master.units.index');
    }
}