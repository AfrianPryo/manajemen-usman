<?php

namespace App\Services\Documents\Renderers;

use App\Services\Documents\Contracts\DocumentBodyRendererInterface;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

class SuratKeteranganBodyRenderer extends AbstractBodyRenderer implements DocumentBodyRendererInterface
{
    public function render(Section $section, array $data, array $params): void
    {
        $this->addParagraph($section, 'Yang bertanda tangan di bawah ini menerangkan dengan sebenarnya bahwa:');

        $this->addSpacer($section);

        $table = $section->addTable(['cellMargin' => 40]);

        $fields = [
            'Nama' => $data['nama_penerima'] ?? '-',
            'Jabatan' => $data['jabatan_penerima'] ?? '-',
            'NIP' => $data['nip_penerima'] ?? '-',
            'Unit Usaha' => $data['unit_usaha'] ?? '-',
        ];

        foreach ($fields as $label => $value) {
            $table->addRow();
            $table->addCell(2200)->addText($label, $this->fontStyle());
            $table->addCell(300)->addText(':', $this->fontStyle());
            $table->addCell(6000)->addText($value, $this->fontStyle());
        }

        $this->addSpacer($section);

        $this->addParagraph($section, 'adalah benar untuk keperluan ' . ($data['keperluan'] ?? '-') . '.');

        $this->addSpacer($section);

        $this->addParagraph($section, $data['isi_keterangan'] ?? '-');

        $this->addSpacer($section);

        $this->addParagraph($section, 'Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.', [], ['alignment' => Jc::BOTH]);
    }
}