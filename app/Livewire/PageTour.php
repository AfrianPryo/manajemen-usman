<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\PageTours;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

/**
 * Tutorial kontekstual per halaman/aksi. Dipasang di view terkait:
 *
 *     <livewire:page-tour tour="documents.generate" />
 *
 * Muncul otomatis SEKALI per akun (disimpan di users.completed_tours), lalu bisa
 * diputar ulang lewat <x-tour-replay tour="..." />. Langkah didefinisikan di
 * App\Support\PageTours. Penempatan kartu & sorotan dikerjakan Alpine di sisi
 * browser; komponen ini hanya menentukan "tampil otomatis atau tidak" dan
 * menyimpan status selesai.
 */
class PageTour extends Component
{
    public string $tour = '';

    public bool $autoStart = false;

    public function mount(string $tour): void
    {
        $this->tour = $tour;

        /** @var User|null $user */
        $user = Auth::user();

        $this->autoStart = PageTours::has($tour)
            && $user
            && ! $user->hasCompletedTour($tour);

        // Tampil otomatis SEKALI saja: dicatat begitu pertama kali muncul, bukan
        // menunggu user menekan Selesai/Lewati -- kalau tab ditutup di tengah
        // jalan, tutorial tidak muncul lagi di kunjungan berikutnya. Putar ulang
        // manual lewat tombol "Panduan" tetap bisa kapan saja.
        if ($this->autoStart) {
            $user->markTourCompleted($tour);
        }
    }

    public function complete(): void
    {
        if (! PageTours::has($this->tour)) {
            return;
        }

        /** @var User|null $user */
        $user = Auth::user();

        $user?->markTourCompleted($this->tour);
        $this->autoStart = false;
    }

    public function render()
    {
        return view('livewire.page-tour', [
            'steps' => PageTours::steps($this->tour),
            'title' => PageTours::title($this->tour),
        ]);
    }
}