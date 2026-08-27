<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\Generate as MasterGenerate;
use App\Models\Asset;
use App\Models\SignatureProfile;
use App\Support\DocumentTypes;
use Illuminate\Support\Facades\Auth;

/**
 * Versi Unit dari "Buat Dokumen Resmi".
 *
 * Reuse: semua property form, method save/validate, dan proses generate
 * dokumen dari class induk (Master\Documents\Generate) dipakai apa adanya —
 * TIDAK di-override, supaya logic penomoran & pembuatan dokumen tetap
 * satu sumber kebenaran.
 *
 * Yang diubah HANYA dua hal:
 * 1. unit_id dikunci saat mount(), tidak bisa diganti user dari form.
 * 2. render() tidak mengirim daftar seluruh unit (props 'units' dihapus),
 *    dan daftar aset difilter ke aset milik unit ybs saja.
 */
class Generate extends MasterGenerate
{
    public function mount(): void
    {
        // Kunci unit_id ke unit milik user yang sedang login.
        // Form tidak menampilkan dropdown pemilihan unit untuk role ini
        // (lihat guard @role di blade generate.blade.php).
        $this->unit_id = Auth::user()->unit_id;
    }

    public function render()
    {
        return view('livewire.master.documents.generate', [
            'templates'  => $this->templatesForType(),
            'assets'     => $this->type === DocumentTypes::BERITA_ACARA_ASET
                ? Asset::where('unit_id', Auth::user()->unit_id)->orderBy('name')->get()
                : collect(),
            'signatures' => SignatureProfile::where('user_id', Auth::id())->get(),
            // 'units' sengaja tidak dikirim: blade harus menyembunyikan
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
        $this->unit_id = Auth::user()->unit_id;
    }
}