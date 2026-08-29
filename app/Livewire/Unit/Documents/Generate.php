<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\Generate as MasterGenerate;
use App\Models\SignatureProfile;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Buat Dokumen Resmi".
 *
 * Reuse: semua property form, method save/validate, dan proses generate
 * dokumen dari class induk (Master\Documents\Generate) dipakai apa adanya —
 * TIDAK di-override, supaya logic penomoran & pembuatan dokumen tetap
 * satu sumber kebenaran.
 *
 * Yang diubah:
 * 1. unit_id dikunci saat mount(), tidak bisa diganti user dari form.
 * 2. render() tidak mengirim daftar seluruh unit (props 'units' dihapus).
 * 3. Daftar aset untuk jenis dokumen "Berita Acara Aset" SENGAJA dikosongkan
 *    (bukan Asset::where('unit_id', ...) seperti sebelumnya) karena tabel
 *    `assets` TIDAK PUNYA kolom unit_id (lihat catatan yang sama di
 *    Unit\Asset\Index) — query lama akan melempar SQL error "Unknown
 *    column 'unit_id'" setiap kali dijalankan. Sampai penautan aset<->unit
 *    tersedia, admin unit belum bisa memilih aset spesifik untuk jenis
 *    dokumen ini.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Generate extends MasterGenerate
{
    use ScopedToUnit;

    public function mount(): void
    {
        // Kunci unit_id ke unit milik user yang sedang login.
        // Form tidak menampilkan dropdown pemilihan unit untuk role ini
        // (lihat guard @role di blade generate.blade.php).
        $this->unit_id = $this->currentUnitId();
    }

    public function render()
    {
        return view('livewire.master.documents.generate', [
            'templates'  => $this->templatesForType(),
            // TODO: Asset belum punya kolom unit_id, jadi belum bisa
            // difilter per unit. Lihat catatan class di atas & TODO di
            // Unit\Asset\Index sebelum mengisi ulang query ini.
            'assets'     => collect(),
            'signatures' => SignatureProfile::where('user_id', Auth::id())->get(),
            // 'units' sengaja tidak dikirim: blade menyembunyikan
            // dropdown unit untuk role unit-admin (lihat catatan blade).
        ]);
    }

    /**
     * Jaga-jaga: kalau ada percobaan mengubah unit_id lewat request
     * manipulation (mis. lewat browser devtools), paksa balik ke unit
     * milik user login sebelum tervalidasi & tersimpan ke dokumen.
     */
    public function updatedUnitId(): void
    {
        $this->unit_id = $this->currentUnitId();
    }
}