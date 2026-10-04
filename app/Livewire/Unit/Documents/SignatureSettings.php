<?php

namespace App\Livewire\Unit\Documents;

use App\Livewire\Master\Documents\SignatureSettings as MasterSignatureSettings;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Pengaturan Tanda Tangan".
 *
 * TIDAK PERLU override apa pun. SignatureProfile di-scope lewat `user_id`
 * (bukan unit_id) di class induk — jadi tiap user, baik master-admin
 * maupun unit-admin, dari awal memang hanya bisa melihat/mengelola profil
 * tanda tangan miliknya sendiri (Auth::id()). Class ini murni ada supaya
 * namespace & folder-nya konsisten dengan pola Unit\Documents\* lain,
 * dan supaya route unit.documents.signature bisa merujuk ke class-nya
 * sendiri (menghindari nge-bind langsung ke class Master di routes/web.php).
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class SignatureSettings extends MasterSignatureSettings
{
    // Trait ini TIDAK mengubah scoping data (profil tanda tangan tetap per
    // user_id). Dipakai agar view bersama (Master & Unit) bisa mengenali
    // konteks Unit dan unit yang sedang dibuka, sehingga tautan "Kembali"
    // tetap menuju route unit.documents.* walau terjadi request Livewire.
    use ScopedToUnit;
}
