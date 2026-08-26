<?php

namespace App\Services\Documents\Providers;

use App\Models\Asset;
use App\Services\Documents\Contracts\DocumentDataProviderInterface;

class AssetHandoverDataProvider implements DocumentDataProviderInterface
{
    public function build(array $params): array
    {
        $assetIds = $params['asset_ids'] ?? [];
        $assets = Asset::whereIn('id', $assetIds)->get();

        $rows = $assets->values()->map(fn ($asset, $i) => [
            'no' => $i + 1,
            'tag_aset' => $asset->asset_tag,
            'nama_aset' => $asset->name,
            'kategori' => $asset->category,
            'kondisi' => match ($asset->condition) {
                'good' => 'Baik',
                'fair' => 'Cukup',
                'damaged' => 'Rusak',
                default => $asset->condition,
            },
            'lokasi' => $asset->location ?? '-',
        ])->toArray();

        return [
            'pihak_pertama_nama' => $params['pihak_pertama_nama'] ?? '-',
            'pihak_pertama_jabatan' => $params['pihak_pertama_jabatan'] ?? '-',
            'pihak_kedua_nama' => $params['pihak_kedua_nama'] ?? '-',
            'pihak_kedua_jabatan' => $params['pihak_kedua_jabatan'] ?? '-',
            'jumlah_aset' => (string) $assets->count(),
            'keperluan' => $params['keperluan'] ?? '-',
            'rows' => $rows,
        ];
    }

    public function availablePlaceholders(): array
    {
        return [
            'pihak_pertama_nama',
            'pihak_pertama_jabatan',
            'pihak_kedua_nama',
            'pihak_kedua_jabatan',
            'jumlah_aset',
            'keperluan',
            'rows (tabel berulang)' => ['no', 'tag_aset', 'nama_aset', 'kategori', 'kondisi', 'lokasi'],
        ];
    }
}
