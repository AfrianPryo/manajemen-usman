<?php

namespace App\Services\Documents;

use App\Models\DocumentNumberSequence;
use App\Models\DocumentTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate nomor surat berikutnya untuk sebuah template, sesuai numbering_format
     * dan numbering_reset (yearly/monthly/never) yang diatur di template tersebut.
     *
     * Format token yang didukung di numbering_format:
     *   {nomor}          -> nomor urut, padded 3 digit (001, 002, ...)
     *   {nomor_polos}     -> nomor urut tanpa padding (1, 2, ...)
     *   {bulan}          -> bulan angka 2 digit (08)
     *   {bulan_romawi}    -> bulan angka romawi (VIII)
     *   {tahun}          -> tahun 4 digit (2026)
     */
    public function generate(DocumentTemplate $template, ?Carbon $date = null): string
    {
        $date = $date ?? now();
        $year = (int) $date->format('Y');
        $month = $template->numbering_reset === 'monthly' ? (int) $date->format('n') : null;

        $nextNumber = DB::transaction(function () use ($template, $year, $month) {
            $sequence = DocumentNumberSequence::where('document_template_id', $template->id)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                $sequence = DocumentNumberSequence::create([
                    'document_template_id' => $template->id,
                    'year' => $year,
                    'month' => $month,
                    'last_number' => 0,
                ]);
            }

            $sequence->increment('last_number');

            return $sequence->last_number;
        });

        return $this->format($template->numbering_format, $nextNumber, $date);
    }

    protected function format(string $pattern, int $number, Carbon $date): string
    {
        $romanMonths = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        $replacements = [
            '{nomor}' => str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            '{nomor_polos}' => (string) $number,
            '{bulan}' => $date->format('m'),
            '{bulan_romawi}' => $romanMonths[$date->month - 1],
            '{tahun}' => $date->format('Y'),
        ];

        return strtr($pattern, $replacements);
    }
}
