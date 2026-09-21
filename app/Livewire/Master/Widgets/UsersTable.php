<?php

namespace App\Livewire\Master\Widgets;

use App\Models\User;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tabel "Admin & Hak Akses" di Dashboard Master Admin.
 *
 * DIPISAH dari App\Livewire\Master\Dashboard (audit performa poin 1 & 3):
 *
 *  1. Sebelumnya Dashboard memuat SELURUH baris User dengan ->get() lalu
 *     me-render semuanya menjadi <tr>. Ratusan/ribuan admin = RAM server
 *     besar + DOM raksasa + payload diff Livewire yang berat di setiap
 *     interaksi. Sekarang dibatasi ->paginate(10).
 *
 *  2. Karena berdiri sebagai komponen sendiri, mengetik di kotak pencarian
 *     admin (atau berpindah halaman tabel) HANYA me-render ulang komponen
 *     ini -- grafik, kartu unit usaha, dan agregat omzet di Dashboard induk
 *     tidak ikut dihitung & dikirim ulang. Begitu pula sebaliknya: saat
 *     Dashboard induk me-render ulang (ganti filter periode, buka modal),
 *     tabel ini TIDAK ikut dirender paksa karena tidak menerima prop apa pun
 *     dari induk.
 *
 * Catatan: komponen ini sengaja TIDAK menerima parameter dari induk supaya
 * Livewire tidak menandainya "dirty" setiap kali induk render.
 */
class UsersTable extends Component
{
    use WithPagination;

    /**
     * Tetap memakai nama query string yang sama ('q_admin') seperti versi
     * lama di Dashboard, supaya URL/bookmark yang sudah beredar tidak rusak.
     */
    #[Url(as: 'q_admin', history: true)]
    public string $searchAdmin = '';

    /**
     * Jumlah baris per halaman. Kecil karena ini hanya ringkasan di
     * dashboard -- daftar lengkap ada di menu "Manajemen Admin".
     */
    public int $perPage = 10;

    /**
     * Kunci session yang dipakai bersama oleh Dashboard induk. Dashboard
     * membutuhkan nilai pencarian ini saat mengekspor laporan, tapi kalau
     * nilainya dikirim lewat event/prop Livewire, induk akan ikut render
     * ulang setiap ketikan -- persis beban yang mau dihilangkan. Jadi
     * nilainya "dititipkan" lewat session saja.
     */
    public const SESSION_SEARCH_KEY = 'dashboard_filter.search_admin';

    public function mount(): void
    {
        // Pulihkan pencarian terakhir kalau user balik ke dashboard lewat
        // link biasa (tanpa query string), konsisten dengan perilaku filter
        // periode di Dashboard induk.
        if (! request()->has('q_admin')) {
            $this->searchAdmin = (string) session(self::SESSION_SEARCH_KEY, '');
        }

        session([self::SESSION_SEARCH_KEY => $this->searchAdmin]);
    }

    /**
     * Wajib reset ke halaman 1 setiap kali kata kunci berubah, kalau tidak
     * user bisa "terjebak" di halaman 5 yang kosong setelah memfilter.
     */
    public function updatingSearchAdmin(): void
    {
        $this->resetPage();
    }

    public function updatedSearchAdmin(): void
    {
        session([self::SESSION_SEARCH_KEY => $this->searchAdmin]);
    }

    public function render()
    {
        $users = User::query()
            // unit  -> dipakai kolom "Unit Kerja"  ({{ $user->unit->name }})
            // roles -> dipakai isMasterAdmin() (hasRole) di kolom "Akses".
            // Keduanya WAJIB di-eager-load; tanpa ini setiap baris memicu
            // query tambahan (N+1). Kolom sengaja TIDAK dibatasi dengan
            // select() agar accessor & helper model (mis. isMasterAdmin())
            // tetap berfungsi persis seperti sebelumnya -- lagipula dengan
            // hanya 10 baris per halaman, membatasi kolom tidak lagi relevan.
            ->with(['unit', 'roles'])
            ->when($this->searchAdmin !== '', function ($query) {
                // Dibungkus closure sendiri supaya orWhere tidak "bocor"
                // dan menganulir kondisi lain kalau nanti ada filter tambahan.
                $query->where(function ($q) {
                    $term = '%' . $this->searchAdmin . '%';

                    $q->where('name', 'like', $term)
                      ->orWhere('username', 'like', $term)
                      ->orWhere('email', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.master.widgets.users-table', [
            'users' => $users,
        ]);
    }
}
