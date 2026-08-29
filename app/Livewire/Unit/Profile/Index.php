<?php

namespace App\Livewire\Unit\Profile;

use App\Livewire\Master\Profile\Index as MasterProfileIndex;
use Livewire\Attributes\Layout;

/**
 * Versi Unit dari "Profil Saya".
 *
 * Sama seperti Unit\Documents\SignatureSettings: TIDAK PERLU override.
 * Seluruh logic di class induk (Master\Profile\Index) sudah murni
 * berbasis auth()->user() — ganti nama, email, no. HP, foto, dan ganti
 * password — tidak ada satupun query yang menyentuh data unit lain.
 * Class ini hanya membungkus ulang supaya folder & route-nya konsisten
 * dengan pola Unit\* lainnya (unit.profile.index).
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
class Index extends MasterProfileIndex
{
    //
}
