<?php

namespace App\Livewire\Master\LogArchives;

use App\Models\AuditLog;
use App\Models\LogArchive;
use App\Models\Setting;
use App\Services\LogArchiveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman "Arsip Log": daftar file arsip Excel per bulan (log login & audit
 * log) hasil rotasi otomatis App\Services\LogArchiveService, plus download.
 *
 * File disimpan di disk 'local' (private) dan hanya bisa diunduh lewat
 * method download() di bawah -- yang mencari file berdasarkan ID di tabel
 * log_archives (bukan path dari klien), jadi tidak ada celah path traversal.
 */
#[Layout('components.layouts.app')]
#[Title('Arsip Log')]
class Index extends Component
{
    use WithPagination;

    public string $typeFilter = '';
    public string $yearFilter = '';

    // Input batas retensi (hari). Sengaja tanpa type int: input number yang
    // dikosongkan mengirim '' dan akan error di properti bertipe int; validasi
    // 'integer' di saveRetention() yang menjaga. Setting-nya SAMA dengan yang
    // ada di Pengaturan > Fitur & Modul (key 'log_retention_days').
    public $retentionInput = LogArchiveService::DEFAULT_RETENTION_DAYS;

    public function mount(): void
    {
        $this->retentionInput = app(LogArchiveService::class)->retentionDays();
    }

    public function saveRetention(): void
    {
        $this->validate([
            'retentionInput' => 'required|integer|min:' . LogArchiveService::MIN_RETENTION_DAYS . '|max:' . LogArchiveService::MAX_RETENTION_DAYS,
        ], [
            'retentionInput.required' => 'Batas retensi wajib diisi.',
            'retentionInput.integer'  => 'Batas retensi harus berupa angka bulat (hari).',
            'retentionInput.min'      => 'Batas retensi minimal ' . LogArchiveService::MIN_RETENTION_DAYS . ' hari.',
            'retentionInput.max'      => 'Batas retensi maksimal ' . LogArchiveService::MAX_RETENTION_DAYS . ' hari.',
        ]);

        $old = app(LogArchiveService::class)->retentionDays();
        $new = (int) $this->retentionInput;

        Setting::set('log_retention_days', $new);
        $this->retentionInput = $new;

        if ($old !== $new) {
            AuditLog::record(
                event: 'SETTINGS_UPDATED',
                identifier: Auth::user()->username ?? null,
                description: 'Admin master mengubah batas retensi log dari halaman Arsip Log.',
                oldValues: ['log_retention_days' => $old],
                newValues: ['log_retention_days' => $new],
            );
        }

        session()->flash('success', "Batas retensi log diperbarui menjadi {$new} hari. Berlaku pada pengarsipan berikutnya.");
    }

    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingYearFilter(): void { $this->resetPage(); }

    public function download(int $id)
    {
        $archive = LogArchive::findOrFail($id);
        $disk    = Storage::disk(LogArchiveService::DISK);

        if (! $disk->exists($archive->file_path)) {
            session()->flash('error', "File arsip {$archive->file_name} tidak ditemukan di server. Kemungkinan terhapus atau dipindahkan -- pulihkan dari backup storage/app/private/log-archives.");

            return null;
        }

        AuditLog::record(
            event: 'LOG_ARCHIVE_DOWNLOADED',
            identifier: $archive->file_name,
            description: "Admin mengunduh arsip {$archive->type_label} periode {$archive->period}.",
        );

        return $disk->download($archive->file_path, $archive->file_name);
    }

    /**
     * Jalankan pengarsipan sekarang (di luar jadwal harian). Berguna kalau
     * scheduler/cron belum jalan, atau setelah mengubah batas retensi.
     */
    public function runArchiveNow(): void
    {
        // Run pertama dengan backlog besar bisa lama; jangan mati di tengah jalan.
        @set_time_limit(0);

        $result = app(LogArchiveService::class)->run();

        if ($result['locked']) {
            session()->flash('error', 'Pengarsipan lain sedang berjalan. Coba lagi beberapa saat lagi.');

            return;
        }

        $files = count($result['archived']);
        $rows  = array_sum(array_column($result['archived'], 'rows'));

        if ($result['failed'] !== []) {
            $detail = collect($result['failed'])
                ->map(fn ($f) => (LogArchive::typeLabels()[$f['type']] ?? $f['type']) . " {$f['period']}: {$f['error']}")
                ->implode('; ');

            session()->flash('error', "Sebagian pengarsipan gagal (data terkait TIDAK dihapus dari tabel utama). {$detail}");
        }

        if ($files > 0) {
            session()->flash('success', "Selesai: {$files} file arsip dibuat, " . number_format($rows, 0, ',', '.') . ' baris dipindahkan dari tabel utama.');
        } elseif ($result['failed'] === []) {
            session()->flash('success', 'Tidak ada data yang perlu diarsipkan saat ini.');
        }

        $this->resetPage();
    }

    public function render()
    {
        $service = app(LogArchiveService::class);

        $archives = LogArchive::query()
            ->when($this->typeFilter, fn ($q, $v) => $q->where('type', $v))
            ->when($this->yearFilter, fn ($q, $v) => $q->where('period', 'like', $v . '-%'))
            ->orderByDesc('period')
            ->orderBy('type')
            ->orderByDesc('id')
            ->paginate(15);

        $years = LogArchive::query()
            ->pluck('period')
            ->map(fn ($p) => substr($p, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        return view('livewire.master.log-archives.index', [
            'archives'      => $archives,
            'years'         => $years,
            'totalFiles'    => LogArchive::count(),
            'totalRows'     => (int) LogArchive::sum('row_count'),
            'totalSize'     => (int) LogArchive::sum('file_size'),
            'retentionDays' => $service->retentionDays(),
            'cutoff'        => $service->cutoff(),
            'pending'       => $service->pendingCounts(),
        ]);
    }
}
