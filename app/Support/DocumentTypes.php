<?php

namespace App\Support;

class DocumentTypes
{
    public const FINANCE_REPORT = 'finance_report';
    public const SURAT_KETERANGAN = 'surat_keterangan';
    public const BERITA_ACARA_ASET = 'berita_acara_aset';
    public const LAPORAN_KONSOLIDASI = 'laporan_konsolidasi';

    /**
     * Daftar jenis dokumen resmi yang didukung, key => label untuk ditampilkan di UI.
     */
    public static function all(): array
    {
        return [
            self::FINANCE_REPORT => 'Laporan Keuangan Resmi',
            self::SURAT_KETERANGAN => 'Surat Keterangan / Pengantar',
            self::BERITA_ACARA_ASET => 'Berita Acara Serah Terima Aset',
            self::LAPORAN_KONSOLIDASI => 'Laporan Konsolidasi Semua Unit',
        ];
    }

    public static function label(string $type): string
    {
        return self::all()[$type] ?? $type;
    }
}
