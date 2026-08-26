<?php

namespace App\Services\Documents\Renderers;

use App\Services\Documents\Contracts\DocumentBodyRendererInterface;
use PhpOffice\PhpWord\Element\Section;

class AssetHandoverBodyRenderer extends AbstractBodyRenderer implements DocumentBodyRendererInterface
{
    public function render(Section $section, array $data, array $params): void
    {
        $this->addParagraph($section, sprintf(
            'Pada hari ini, %s, yang bertanda tangan di bawah ini:',
            now()->translatedFormat('d F Y')
        ));

        $this->addSpacer($section);

        $this->addParagraph($section, sprintf(
            '1. %s, selaku %s, selanjutnya disebut Pihak Pertama.',
            $data['pihak_pertama_nama'] ?? '-',
            $data['pihak_pertama_jabatan'] ?? '-'
        ), [], ['spaceAfter' => 60]);

        $this->addParagraph($section, sprintf(
            '2. %s, selaku %s, selanjutnya disebut Pihak Kedua.',
            $data['pihak_kedua_nama'] ?? '-',
            $data['pihak_kedua_jabatan'] ?? '-'
        ));

        $this->addSpacer($section);

        $this->addParagraph($section, sprintf(
            'Menyatakan telah melaksanakan serah terima %s aset dari Pihak Pertama kepada Pihak Kedua untuk keperluan %s, dengan rincian sebagai berikut:',
            $data['jumlah_aset'] ?? '0',
            $data['keperluan'] ?? '-'
        ));

        $this->addSpacer($section);

        $this->addDataTable($section, [
            'no' => 'No',
            'tag_aset' => 'Tag Aset',
            'nama_aset' => 'Nama Aset',
            'kategori' => 'Kategori',
            'kondisi' => 'Kondisi',
            'lokasi' => 'Lokasi',
        ], $data['rows'] ?? []);

        $this->addSpacer($section);

        $this->addParagraph($section, 'Demikian berita acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.');
    }
}