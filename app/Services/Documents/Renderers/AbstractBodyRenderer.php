<?php

namespace App\Services\Documents\Renderers;

use App\Services\Documents\Support\SanitizesDocumentText;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;

abstract class AbstractBodyRenderer
{
    use SanitizesDocumentText;

    protected function fontStyle(array $override = []): array
    {
        return array_merge(['name' => 'Arial', 'size' => 11], $override);
    }

    protected function paragraphStyle(array $override = []): array
    {
        return array_merge(['spaceAfter' => 160, 'alignment' => Jc::BOTH], $override);
    }

    protected function addParagraph(Section $section, string $text, array $fontOverride = [], array $paragraphOverride = []): void
    {
        $section->addText($this->sanitize($text), $this->fontStyle($fontOverride), $this->paragraphStyle($paragraphOverride));
    }

    protected function addSpacer(Section $section, int $lines = 1): void
    {
        $section->addTextBreak($lines);
    }

    /**
     * Bikin tabel data dari daftar kolom (key di $rows => label header) dan array baris.
     * Kolom 'no' otomatis dibuat sempit & rata tengah. Kalau $rows kosong, tabel tetap
     * tampil dengan satu baris "Tidak ada data." supaya layout surat tidak pincang.
     */
    protected function addDataTable(Section $section, array $columns, array $rows): void
    {
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 60,
        ]);

        $table->addRow(null, ['tblHeader' => true]);
        foreach ($columns as $key => $label) {
            $width = $key === 'no' ? 500 : null;
            $table->addCell($width, ['bgColor' => 'EEEEEE'])
                ->addText($this->sanitize($label), $this->fontStyle(['bold' => true]), ['alignment' => Jc::CENTER]);
        }

        if (empty($rows)) {
            $table->addRow();
            $table->addCell(null, ['gridSpan' => count($columns)])
                ->addText('Tidak ada data.', $this->fontStyle(), ['alignment' => Jc::CENTER]);

            return;
        }

        foreach ($rows as $row) {
            $table->addRow();
            foreach (array_keys($columns) as $key) {
                $align = $key === 'no' ? Jc::CENTER : Jc::START;
                $value = $this->sanitize((string) ($row[$key] ?? '-'));
                $table->addCell(null)->addText($value, $this->fontStyle(), ['alignment' => $align]);
            }
        }
    }
}