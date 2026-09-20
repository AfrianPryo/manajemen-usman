<?php

namespace App\Services;

use App\Exports\AuditLogExport;
use App\Exports\AuthLogExport;
use App\Models\AuditLog;
use App\Models\AuthLog;
use App\Models\LogArchive;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Throwable;
use ZipArchive;

/**
 * "Log rotation" untuk tabel auth_logs & audit_logs:
 *   1. EKSPOR bulan-bulan lama ke file Excel (memakai ulang AuthLogExport &
 *      AuditLogExport yang sama dengan halaman Export Data),
 *   2. VERIFIKASI file-nya (jumlah baris harus cocok dengan isi tabel),
 *   3. baru HAPUS baris-barisnya dari tabel utama.
 *
 * Urutan itu yang bikin aman: kalau langkah 1/2 gagal, tidak ada satu baris
 * pun yang dihapus. File disimpan di disk 'local' (storage/app/private, TIDAK
 * bisa diakses langsung lewat URL) dengan struktur:
 *
 *   log-archives/auth-logs/2026/log-login-2026-06.xlsx
 *   log-archives/audit-logs/2026/audit-log-2026-06.xlsx
 *
 * ATURAN RETENSI (penting): pengarsipan dilakukan per BULAN PENUH supaya satu
 * bulan = satu file yang utuh (tidak pecah/ditimpa). Sebuah bulan baru
 * diarsipkan setelah SELURUH bulan itu lebih tua dari batas retensi. Jadi
 * "retensi N hari" artinya: data hidup di tabel utama MINIMAL N hari, dan
 * paling lama sekitar N + 31 hari sampai bulannya lengkap terlewati.
 */
class LogArchiveService
{
    public const DISK = 'local';
    public const BASE_DIR = 'log-archives';

    public const DEFAULT_RETENTION_DAYS = 90;
    public const MIN_RETENTION_DAYS = 7;
    public const MAX_RETENTION_DAYS = 3650;

    private const LOCK_KEY = 'log-archive:run';

    /**
     * Batas retensi (hari) dari Pengaturan > Fitur & Modul, di-clamp ke
     * rentang yang wajar supaya nilai rusak tidak menghapus terlalu agresif.
     */
    public function retentionDays(): int
    {
        $days = (int) Setting::get('log_retention_days', self::DEFAULT_RETENTION_DAYS);

        if ($days < 1) {
            return self::DEFAULT_RETENTION_DAYS;
        }

        return max(self::MIN_RETENTION_DAYS, min($days, self::MAX_RETENTION_DAYS));
    }

    /**
     * Semua baris dengan created_at SEBELUM titik ini akan diarsipkan.
     * = awal bulan dari (sekarang - N hari), lihat "ATURAN RETENSI" di atas.
     */
    public function cutoff(?int $days = null): Carbon
    {
        return now()->subDays($days ?? $this->retentionDays())->startOfMonth();
    }

    /**
     * Jumlah baris yang saat ini sudah memenuhi syarat diarsipkan (per jenis).
     */
    public function pendingCounts(?int $days = null): array
    {
        $cutoff = $this->cutoff($days);

        $counts = [];
        foreach ($this->types() as $type => $cfg) {
            $counts[$type] = $cfg['model']::query()->where('created_at', '<', $cutoff)->count();
        }

        return $counts;
    }

    /**
     * Jalankan pengarsipan.
     *
     * @param  int|null    $days   Timpa batas retensi hanya untuk eksekusi ini
     * @param  string|null $only   'auth' | 'audit' | null (keduanya)
     * @param  bool        $dryRun Hanya hitung, tidak menulis file / menghapus data
     * @return array{locked: bool, cutoff: Carbon, retention_days: int, archived: array, planned: array, failed: array}
     */
    public function run(?int $days = null, ?string $only = null, bool $dryRun = false): array
    {
        $days ??= $this->retentionDays();

        $result = [
            'locked'         => false,
            'cutoff'         => $this->cutoff($days),
            'retention_days' => $days,
            'archived'       => [],
            'planned'        => [],
            'failed'         => [],
        ];

        // Cegah dua proses jalan bersamaan (scheduler vs tombol manual).
        $lock = Cache::lock(self::LOCK_KEY, 3600);
        if (! $lock->get()) {
            $result['locked'] = true;

            return $result;
        }

        try {
            foreach ($this->types() as $type => $cfg) {
                if ($only !== null && $only !== $type) {
                    continue;
                }

                foreach ($this->monthWindows($cfg['model'], $result['cutoff']) as [$start, $end]) {
                    try {
                        $item = $this->archiveMonth($type, $cfg, $start, $end, $dryRun);
                    } catch (Throwable $e) {
                        Log::error('Pengarsipan log gagal', [
                            'type'   => $type,
                            'period' => $start->format('Y-m'),
                            'error'  => $e->getMessage(),
                        ]);

                        $result['failed'][] = [
                            'type'   => $type,
                            'period' => $start->format('Y-m'),
                            'error'  => $e->getMessage(),
                        ];

                        continue;
                    }

                    if ($item === null) {
                        continue; // bulan ini kosong
                    }

                    $result[$dryRun ? 'planned' : 'archived'][] = $item;
                }
            }
        } finally {
            $lock->release();
        }

        return $result;
    }

    // =========================================================================
    // Internal
    // =========================================================================

    /**
     * Konfigurasi per jenis log. 'filters' = filter tambahan untuk class Export.
     */
    private function types(): array
    {
        return [
            LogArchive::TYPE_AUTH => [
                'model'   => AuthLog::class,
                'export'  => AuthLogExport::class,
                'dir'     => 'auth-logs',
                'prefix'  => 'log-login',
                'label'   => 'Log Aktivitas Login',
                'filters' => [],
            ],
            LogArchive::TYPE_AUDIT => [
                'model'   => AuditLog::class,
                'export'  => AuditLogExport::class,
                'dir'     => 'audit-logs',
                'prefix'  => 'audit-log',
                'label'   => 'Audit Log Sistem',
                // Halaman Audit Log menyembunyikan event login/logout, tapi
                // baris-baris itu tetap ada di tabel dan ikut dihapus. Karena
                // itu arsip WAJIB memuat semuanya, kalau tidak datanya hilang.
                'filters' => ['includeAuthEvents' => true],
            ],
        ];
    }

    /**
     * Daftar jendela bulan [awal, akhir) dari bulan terlama yang punya data
     * sampai tepat sebelum $cutoff. Pakai rentang tanggal biasa (bukan fungsi
     * tanggal SQL) supaya jalan sama di SQLite/MySQL/PostgreSQL.
     *
     * @return \Generator<int, array{0: Carbon, 1: Carbon}>
     */
    private function monthWindows(string $modelClass, Carbon $cutoff): \Generator
    {
        $oldest = $modelClass::query()->where('created_at', '<', $cutoff)->min('created_at');

        if (! $oldest) {
            return;
        }

        $cursor = Carbon::parse($oldest)->startOfMonth();

        while ($cursor->lt($cutoff)) {
            yield [$cursor->copy(), $cursor->copy()->addMonth()];

            $cursor->addMonth();
        }
    }

    private function archiveMonth(string $type, array $cfg, Carbon $start, Carbon $end, bool $dryRun): ?array
    {
        $model = $cfg['model'];

        // "Snapshot" bulan ini: id tertinggi dikunci, supaya baris yang
        // diekspor PERSIS sama dengan yang nanti dihapus.
        $window = fn () => $model::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<', $end);

        $maxId = (int) $window()->max('id');
        if ($maxId === 0) {
            return null;
        }

        $rows   = $window()->where('id', '<=', $maxId)->count();
        $period = $start->format('Y-m');

        if ($dryRun) {
            return ['type' => $type, 'period' => $period, 'rows' => $rows];
        }

        $disk = Storage::disk(self::DISK);

        // 1) Ekspor ke file sementara, verifikasi, baru pindah ke lokasi final.
        $tmpPath   = self::BASE_DIR . '/.tmp/' . Str::uuid() . '.xlsx';
        $finalPath = $this->uniquePath($cfg, $start);

        try {
            Excel::store(new $cfg['export']($cfg['filters'] + [
                'createdFrom'   => $start->toDateTimeString(),
                'createdBefore' => $end->toDateTimeString(),
                'maxId'         => $maxId,
            ]), $tmpPath, self::DISK);

            $this->assertFileHasRows($disk->path($tmpPath), $rows);

            $disk->move($tmpPath, $finalPath);
        } catch (Throwable $e) {
            $disk->delete($tmpPath);
            $disk->delete($finalPath);

            throw $e;
        }

        // 2) Catat arsip + hapus dari tabel utama dalam SATU transaksi.
        try {
            DB::transaction(function () use ($type, $period, $finalPath, $rows, $disk, $window, $maxId) {
                LogArchive::create([
                    'type'      => $type,
                    'period'    => $period,
                    'file_path' => $finalPath,
                    'file_name' => basename($finalPath),
                    'row_count' => $rows,
                    'file_size' => (int) $disk->size($finalPath),
                ]);

                $window()->where('id', '<=', $maxId)->delete();
            });
        } catch (Throwable $e) {
            // Data masih utuh di tabel, jadi file yatim ini tidak dibutuhkan.
            $disk->delete($finalPath);

            throw $e;
        }

        $this->recordAudit($cfg['label'], $period, $rows, basename($finalPath));

        return [
            'type'   => $type,
            'period' => $period,
            'rows'   => $rows,
            'file'   => $finalPath,
        ];
    }

    /**
     * Path final file arsip. Kalau sudah ada file dengan nama sama (mis.
     * sisa eksekusi yang terhenti sebelum sempat menghapus baris), file lama
     * TIDAK ditimpa -- diberi akhiran -2, -3, dst.
     */
    private function uniquePath(array $cfg, Carbon $start): string
    {
        $disk = Storage::disk(self::DISK);
        $dir  = self::BASE_DIR . '/' . $cfg['dir'] . '/' . $start->format('Y');
        $name = $cfg['prefix'] . '-' . $start->format('Y-m');

        $path = "{$dir}/{$name}.xlsx";
        $i    = 2;

        while ($disk->exists($path)) {
            $path = "{$dir}/{$name}-{$i}.xlsx";
            $i++;
        }

        return $path;
    }

    /**
     * Pastikan file xlsx benar-benar berisi seluruh baris (1 baris judul +
     * $expectedRows baris data). Dihitung langsung dari XML sheet pertama
     * secara streaming, jadi tidak memuat seluruh file ke memori.
     */
    private function assertFileHasRows(string $absolutePath, int $expectedRows): void
    {
        if (! is_file($absolutePath) || filesize($absolutePath) === 0) {
            throw new RuntimeException('File arsip tidak terbentuk.');
        }

        $zip = new ZipArchive();
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('File arsip tidak bisa dibuka untuk verifikasi.');
        }

        $stream = $zip->getStream('xl/worksheets/sheet1.xml');
        if (! $stream) {
            $zip->close();
            throw new RuntimeException('Sheet data tidak ditemukan di file arsip.');
        }

        $count = 0;
        $carry = '';

        while (! feof($stream)) {
            $chunk = $carry . fread($stream, 1024 * 1024);
            $count += substr_count($chunk, '<row ');
            // sisakan ekor pendek supaya tag yang terpotong di batas chunk tetap terhitung sekali
            $carry = substr($chunk, -4);
        }

        fclose($stream);
        $zip->close();

        if ($count < $expectedRows + 1) {
            throw new RuntimeException(
                "Verifikasi gagal: file berisi {$count} baris, seharusnya minimal " . ($expectedRows + 1) . ' (termasuk judul).'
            );
        }
    }

    private function recordAudit(string $label, string $period, int $rows, string $fileName): void
    {
        try {
            AuditLog::record(
                event: 'LOG_ARCHIVED',
                identifier: $fileName,
                description: "{$label} periode {$period}: {$rows} baris diarsipkan ke Excel lalu dihapus dari tabel utama.",
            );
        } catch (Throwable $e) {
            Log::warning('Gagal mencatat audit pengarsipan log: ' . $e->getMessage());
        }
    }
}
