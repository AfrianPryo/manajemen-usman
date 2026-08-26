<?php

namespace App\Services\Documents\Contracts;

interface DocumentDataProviderInterface
{
    /**
     * Tarik & susun data dari sistem menjadi array asosiatif placeholder => value.
     * Boleh menyertakan key 'rows' berisi array baris untuk tabel berulang di template.
     *
     * @param array $params parameter dari form (filter tanggal, unit, id aset, dll.)
     * @return array
     */
    public function build(array $params): array;

    /**
     * Daftar placeholder yang tersedia untuk jenis dokumen ini.
     * Dipakai sebagai "cheat sheet" di halaman Kelola Template.
     *
     * @return array
     */
    public function availablePlaceholders(): array;
}
