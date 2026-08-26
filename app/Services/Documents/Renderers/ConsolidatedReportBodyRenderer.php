<?php

namespace App\Services\Documents\Renderers;

use App\Services\Documents\Contracts\DocumentBodyRendererInterface;
use PhpOffice\PhpWord\Element\Section;

class ConsolidatedReportBodyRenderer extends AbstractBodyRenderer implements DocumentBodyRendererInterface
{
    public function render(Section $section, array $data, array $params): void
    {
        $this->addParagraph($section, sprintf(
            'Bersama ini kami sampaikan Laporan Konsolidasi seluruh unit usaha (%s unit) untuk periode %s, dengan rincian sebagai berikut:',
            $data['jumlah_unit'] ?? '0',
            $data['periode'] ?? 'Seluruh Periode'
        ));

        $this->addSpacer($section);

        $this->addDataTable($section, [
            'no' => 'No',
            'unit' => 'Unit',
            'status' => 'Status',
            'pemasukan' => 'Pemasukan',
            'pengeluaran' => 'Pengeluaran',
            'net' => 'Net',
        ], $data['rows'] ?? []);

        $this->addSpacer($section);

        $this->addParagraph($section, 'Demikian laporan konsolidasi ini kami sampaikan untuk dapat dipergunakan sebagaimana mestinya.');
    }
}