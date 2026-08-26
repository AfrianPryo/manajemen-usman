<?php

namespace App\Services\Documents\Support;

trait SanitizesDocumentText
{
    /**
     * Bersihkan teks sebelum ditulis ke docx supaya tidak merusak XML hasil generate.
     *
     * PhpWord idealnya meng-escape karakter spesial XML (&, <, >) secara otomatis lewat
     * addText(), tapi ini tidak boleh diandalkan sepenuhnya karena teks yang masuk ke sini
     * berasal dari sistem (deskripsi transaksi, nama aset, subjek surat, nama penandatangan,
     * dsb.) dan bisa berisi karakter apa saja - termasuk '&' polos yang pernah bikin file
     * hasil generate corrupt dan tidak bisa dibuka Word
     * ("Word experienced an error trying to open the file").
     *
     * Dua hal yang dibersihkan:
     * 1. Control character yang tidak valid di XML 1.0 (selain tab/LF/CR) - kalau lolos,
     *    juga bisa membuat XML tidak well-formed.
     * 2. Karakter spesial XML (&, <, >, ", ') - di-escape via htmlspecialchars dengan
     *    ENT_XML1 supaya hasilnya valid untuk konteks XML (bukan HTML).
     */
    protected function sanitize(string $text): string
    {
        // buang control character yang tidak valid di XML 1.0
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $text) ?? $text;

        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}