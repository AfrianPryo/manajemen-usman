<?php

namespace App\Services\Documents\Renderers;

use App\Services\Documents\Contracts\DocumentBodyRendererInterface;
use PhpOffice\PhpWord\Element\Section;

class FinanceReportBodyRenderer extends AbstractBodyRenderer implements DocumentBodyRendererInterface
{
    public function render(Section $section, array $data, array $params): void
    {
        $this->addParagraph($section, sprintf(
            'Bersama ini kami sampaikan Laporan Keuangan %s untuk periode %s, dengan rincian sebagai berikut:',
            $data['unit_usaha'] ?? 'Seluruh Unit Usaha',
            $data['periode'] ?? 'Seluruh Periode'
        ));

        $this->addSpacer($section);

        $this->addDataTable($section, [
            'no' => 'No',
            'tanggal' => 'Tanggal',
            'unit' => 'Unit',
            'kategori' => 'Kategori',
            'tipe' => 'Tipe',
            'nominal' => 'Nominal',
            'keterangan' => 'Keterangan',
        ], $data['rows'] ?? []);

        $this->addSpacer($section);

        $this->addParagraph($section, sprintf(
            'Total Pemasukan: %s. Total Pengeluaran: %s. Laba/Rugi Bersih: %s. Jumlah Transaksi: %s.',
            $data['total_pemasukan'] ?? '-',
            $data['total_pengeluaran'] ?? '-',
            $data['laba_bersih'] ?? '-',
            $data['jumlah_transaksi'] ?? '-'
        ), ['bold' => true]);

        $this->addSpacer($section);

        $this->addParagraph($section, 'Demikian laporan keuangan ini kami sampaikan untuk dapat dipergunakan sebagaimana mestinya.');
    }
}