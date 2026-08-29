<?php

namespace App\Livewire\Unit\Asset;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Versi Unit dari "Aset Unit Usaha".
 *
 * SENGAJA belum menampilkan data apa pun, dan SENGAJA tidak extends
 * Master\Asset\Index. Alasannya bukan malas, tapi keamanan data:
 *
 * Model Asset tidak punya kolom `unit_id` (lihat app/Models/Asset.php).
 * Field yang ada cuma `assigned_to` dan `location`, keduanya teks bebas
 * yang diisi manual — tidak ada relasi database yang bisa dipercaya untuk
 * memfilter "aset milik unit ini saja". Kalau class ini extends
 * Master\Asset\Index langsung, seluruh method-nya (list, edit, delete,
 * import, export) akan ikut terwarisi TANPA scoping sama sekali — artinya
 * admin unit manapun bisa melihat dan bahkan MENGHAPUS aset milik unit
 * lain lewat menu ini. Itu lebih berbahaya daripada halaman yang belum
 * berfungsi, jadi untuk sekarang halaman ini murni menampilkan status
 * "belum tersedia" yang jujur.
 *
 * TODO sebelum modul ini diisi: konfirmasi dulu salah satu dari opsi ini
 * (atau lainnya) —
 *   1. Tambah kolom `unit_id` ke tabel `assets` (migrasi baru), lalu
 *      filter Asset::where('unit_id', ...) seperti modul lain, ATAU
 *   2. Filter berbasis teks: assigned_to/location LIKE nama unit
 *      (rawan salah kalau penulisan tidak konsisten, tidak disarankan
 *      untuk keputusan hapus/edit data), ATAU
 *   3. Halaman ini murni read-only (tanpa aksi edit/hapus) sampai opsi
 *      1 tersedia.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Aset Unit Usaha')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.unit.asset.index');
    }
}
