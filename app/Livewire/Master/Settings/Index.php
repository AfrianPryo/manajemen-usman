<?php

namespace App\Livewire\Master\Settings;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Pengaturan Sistem')]
class Index extends Component
{
    public string $schoolName = 'SMK Negeri 1';
    public string $schoolAddress = '';
    public string $schoolPhone = '';
    public string $schoolEmail = '';
    public int $sessionTimeout = 30;
    public bool $enableNotifications = true;

    public function save()
    {
        $this->validate([
            'schoolName' => 'required|string|max:255',
            'schoolAddress' => 'nullable|string|max:500',
            'schoolPhone' => 'nullable|string|max:20',
            'schoolEmail' => 'nullable|email|max:255',
            'sessionTimeout' => 'required|integer|min:5|max:480',
        ]);

        session()->flash('success', 'Pengaturan sistem berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.master.settings.index');
    }
}