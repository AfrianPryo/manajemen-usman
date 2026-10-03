<?php

namespace App\Livewire;

use App\Models\User;
use App\Support\PageTours;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Renderless;
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

        // Server HANYA memutuskan "perlu tampil otomatis atau tidak". Status selesai
        // TIDAK dicatat di sini: kalau dicatat saat mount, tutorial ikut "terpakai"
        // pada render server yang tidak pernah sampai tampil di layar (prefetch
        // wire:navigate, render ulang, JS belum siap), dan user tidak pernah melihatnya.
        // Pencatatan dilakukan browser lewat shown() begitu kartu benar-benar tampil.
        $this->autoStart = PageTours::has($tour)
            && $user
            && ! $user->hasCompletedTour($tour);
    }

    /**
     * Dipanggil browser SEKALI, tepat saat kartu tutorial otomatis benar-benar sudah
     * tampil. Sejak itu tutorial tidak muncul otomatis lagi (walau tab ditutup di
     * tengah jalan); putar ulang manual lewat tombol "Panduan" tetap bisa.
     */
    #[Renderless]
    public function shown(): void
    {
        $this->markDone();
    }

    #[Renderless]
    public function complete(): void
    {
        $this->markDone();
    }

    private function markDone(): void
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
            // Hanya saat URL memakai ?tourdebug=1: tampilkan status tutorial di pojok layar.
            'debug' => request()->boolean('tourdebug')
                ? ['completed' => Auth::user()?->completed_tours ?? []]
                : null,
        ]);
    }
}