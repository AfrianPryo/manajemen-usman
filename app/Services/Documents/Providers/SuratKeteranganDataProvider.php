<?php

namespace App\Services\Documents\Providers;

use App\Models\Unit;
use App\Services\Documents\Contracts\DocumentDataProviderInterface;

class SuratKeteranganDataProvider implements DocumentDataProviderInterface
{
    /**
     * Surat keterangan umumnya berisi data yang sifatnya kasuistik (nama, keperluan, dsb),
     * jadi sebagian besar diisi manual lewat form — bukan ditarik otomatis dari tabel transaksi.
     * unit_usaha tetap ditarik dari master data Unit supaya penulisannya konsisten (anti typo).
     */
    public function build(array $params): array
    {
        $unit = ($params['unit_id'] ?? null) ? Unit::find($params['unit_id']) : null;

        return [
            'nama_penerima' => $params['nama_penerima'] ?? '-',
            'jabatan_penerima' => $params['jabatan_penerima'] ?? '-',
            'nip_penerima' => $params['nip_penerima'] ?? '-',
            'unit_usaha' => $unit->name ?? '-',
            'keperluan' => $params['keperluan'] ?? '-',
            'isi_keterangan' => $params['isi_keterangan'] ?? '-',
        ];
    }

    public function availablePlaceholders(): array
    {
        return [
            'nama_penerima',
            'jabatan_penerima',
            'nip_penerima',
            'unit_usaha',
            'keperluan',
            'isi_keterangan',
        ];
    }
}
