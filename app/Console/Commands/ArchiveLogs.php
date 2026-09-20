<?php

namespace App\Console\Commands;

use App\Models\LogArchive;
use App\Services\LogArchiveService;
use Illuminate\Console\Command;

class ArchiveLogs extends Command
{
    protected $signature = 'logs:archive
                            {--days= : Timpa batas retensi (hari) hanya untuk eksekusi ini}
                            {--type= : Batasi ke satu jenis log: auth atau audit (default: keduanya)}
                            {--dry-run : Hanya tampilkan apa yang akan diarsipkan, tanpa menulis file / menghapus data}';

    protected $description = 'Arsipkan log login & audit log yang lewat batas retensi ke file Excel per bulan, lalu hapus dari tabel utama';

    /**
     * Implementasi lengkap ada di App\Services\LogArchiveService (pola yang
     * sama dengan SendRoutineReport -> RoutineReportService): command ini
     * sengaja "tipis". Terdaftar di routes/console.php (harian jam 02:00).
     */
    public function handle(LogArchiveService $service): int
    {
        $days = null;
        if ($this->option('days') !== null) {
            if (! ctype_digit((string) $this->option('days')) || (int) $this->option('days') < 1) {
                $this->error('--days harus berupa bilangan bulat >= 1.');

                return self::FAILURE;
            }
            $days = (int) $this->option('days');
        }

        $only = $this->option('type');
        if ($only !== null && ! in_array($only, [LogArchive::TYPE_AUTH, LogArchive::TYPE_AUDIT], true)) {
            $this->error('--type harus "auth" atau "audit".');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $result = $service->run($days, $only, $dryRun);

        if ($result['locked']) {
            $this->warn('Pengarsipan lain sedang berjalan, dilewati.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            'Retensi %d hari -> mengarsipkan semua log sebelum %s%s.',
            $result['retention_days'],
            $result['cutoff']->format('d M Y'),
            $dryRun ? ' (DRY RUN)' : ''
        ));

        $rows = $dryRun ? $result['planned'] : $result['archived'];

        if ($rows === [] && $result['failed'] === []) {
            $this->line('Tidak ada data yang perlu diarsipkan.');

            return self::SUCCESS;
        }

        if ($rows !== []) {
            $this->table(
                ['Jenis', 'Periode', 'Baris', $dryRun ? 'Status' : 'File'],
                array_map(fn ($r) => [
                    LogArchive::typeLabels()[$r['type']] ?? $r['type'],
                    $r['period'],
                    number_format($r['rows'], 0, ',', '.'),
                    $dryRun ? 'akan diarsipkan' : $r['file'],
                ], $rows)
            );
        }

        foreach ($result['failed'] as $f) {
            $this->error("GAGAL {$f['type']} {$f['period']}: {$f['error']} (data di tabel utama TIDAK dihapus)");
        }

        return $result['failed'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
