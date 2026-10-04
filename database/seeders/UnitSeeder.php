<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UnitSeeder extends Seeder
{
    /**
     * Folder logo di disk 'public' -- sama dengan yang dipakai form Unit Usaha
     * (App\Livewire\Master\Units\Index: $this->logo->store('unit-logos', 'public')),
     * jadi tampil lewat asset('storage/' . $unit->logo) di halaman Master & landing.
     */
    private const LOGO_DIR = 'unit-logos';

    public function run(): void
    {
        // 'initials' & 'color' dipakai untuk membuat logo SVG bawaan (placeholder).
        // BUG-03 fix: tambah 'category' — sebelumnya field ini tidak diisi sehingga
        // semua unit default 'ritel', termasuk Bengkel yang seharusnya 'jasa'.
        $units = [
            ['name' => 'TEFA',      'department' => 'PPLG',      'description' => 'Teaching Factory', 'initials' => 'TF', 'color' => '#2563EB', 'category' => 'ritel'],
            ['name' => 'Bengkel',   'department' => 'TO',        'description' => 'Unit Bengkel',      'initials' => 'BK', 'color' => '#EA580C', 'category' => 'jasa'],
            ['name' => 'Fotokopi',  'department' => 'MPLB',      'description' => 'Unit Fotokopi',     'initials' => 'FK', 'color' => '#0D9488', 'category' => 'ritel'],
            ['name' => 'Alfamart',  'department' => 'PM',        'description' => 'Unit Alfamart',     'initials' => 'AF', 'color' => '#DC2626', 'category' => 'ritel'],
            ['name' => 'Teh Siswa', 'department' => 'Akuntansi', 'description' => 'Unit Teh Siswa',   'initials' => 'TS', 'color' => '#16A34A', 'category' => 'ritel'],
        ];

        foreach ($units as $data) {
            $slug = Str::slug($data['name']);

            $unit = Unit::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'        => $data['name'],
                    'department'  => $data['department'],
                    'description' => $data['description'],
                    'category'    => $data['category'],
                    'is_active'   => true,
                ]
            );

            $this->seedLogo($unit, $data['initials'], $data['color']);
        }
    }

    /**
     * Isi logo unit. Tidak menimpa logo yang sudah ada (mis. hasil unggahan
     * Master Admin) selama filenya masih ada di storage.
     *
     * Urutan sumber logo:
     *   1. File di database/seeders/logos/{slug}.(png|jpg|jpeg|webp|svg)
     *      -- taruh logo asli unit di sini bila sudah ada.
     *   2. Fallback: logo SVG sederhana (kotak berwarna + inisial) yang dibuat otomatis.
     */
    private function seedLogo(Unit $unit, string $initials, string $color): void
    {
        $disk = Storage::disk('public');

        if ($unit->logo && $disk->exists($unit->logo)) {
            return;
        }

        $source = $this->findLogoFile($unit->slug);

        if ($source) {
            $path = self::LOGO_DIR . '/' . $unit->slug . '.' . pathinfo($source, PATHINFO_EXTENSION);
            $disk->put($path, file_get_contents($source));
        } else {
            $path = self::LOGO_DIR . '/' . $unit->slug . '.svg';
            $disk->put($path, $this->makeSvgLogo($initials, $color));
        }

        $unit->update(['logo' => $path]);
    }

    private function findLogoFile(string $slug): ?string
    {
        foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
            $file = database_path("seeders/logos/{$slug}.{$ext}");

            if (is_file($file)) {
                return $file;
            }
        }

        return null;
    }

    private function makeSvgLogo(string $initials, string $color): string
    {
        $initials = e($initials);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="256" height="256" viewBox="0 0 256 256">
  <rect width="256" height="256" rx="48" fill="{$color}"/>
  <text x="128" y="128" fill="#FFFFFF" font-family="Arial, Helvetica, sans-serif" font-size="104" font-weight="700" text-anchor="middle" dominant-baseline="central">{$initials}</text>
</svg>
SVG;
    }
}