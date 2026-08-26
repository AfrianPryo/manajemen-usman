<?php

namespace App\Services\Documents\Contracts;

use PhpOffice\PhpWord\Element\Section;

interface DocumentBodyRendererInterface
{
    /**
     * Tulis isi (body) surat langsung ke section PhpWord berdasarkan data yang
     * sudah disiapkan oleh Data Provider terkait (lihat DocumentDataProviderInterface::build()).
     *
     * Kop surat (header/footer) sudah ada lebih dulu di $section karena diambil dari
     * file kop surat yang diunggah admin di Kelola Template. Nomor surat, tanggal,
     * kepada-Yth, dan blok tanda tangan ditangani terpisah oleh OfficialDocumentGenerator
     * (sebelum & sesudah method ini dipanggil), jadi renderer di sini cukup fokus
     * menulis badan surat saja (paragraf pembuka, tabel data, paragraf penutup).
     *
     * @param array $data   hasil DataProvider::build() (key 'rows' tetap disertakan)
     * @param array $params parameter mentah dari form Generate.php
     */
    public function render(Section $section, array $data, array $params): void;
}