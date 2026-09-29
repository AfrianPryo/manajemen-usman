<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'identifier',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // CATATAN: model ini SENGAJA tidak lagi memakai MassPrunable. Trait itu
    // menghapus baris >= 90 hari lewat `model:prune` TANPA menyimpan apa pun,
    // sehingga riwayatnya hilang. Pembersihan sekarang ditangani
    // App\Services\LogArchiveService (command `logs:archive`): diekspor ke
    // Excel per bulan dulu, baru dihapus. Batas retensinya diatur di
    // Pengaturan > Fitur & Modul (bukan hard-coded 90 hari lagi).

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Antrian audit log yang menunggu ditulis ke database (satu bulk INSERT
     * per request). Lihat record() & flushPending().
     *
     * @var array<int, array<string, mixed>>
     */
    protected static array $pending = [];

    /**
     * Helper statis untuk mempermudah pencatatan audit log dari mana saja.
     *
     * OPTIMASI (sebelumnya: 1 INSERT sinkron per pemanggilan, di tengah
     * request user): pada request web biasa, baris log TIDAK langsung
     * di-INSERT. Seluruh nilainya (user, IP, user-agent, waktu) dicatat
     * SEKARANG, lalu semua log dalam request yang sama ditulis sekaligus
     * lewat SATU bulk INSERT setelah response dikirim ke browser
     * (terminating callback) -- jadi user tidak menunggu INSERT, dan aksi
     * massal (mis. import ratusan transaksi) tidak lagi menghasilkan
     * ratusan INSERT terpisah.
     *
     * Sengaja TIDAK memakai queue worker (queue:work): kalau worker tidak
     * berjalan, log tidak akan pernah tertulis. Cara ini tidak butuh
     * proses tambahan apa pun.
     *
     * Tetap SINKRON (perilaku lama, tidak berubah) bila:
     *  - sedang di dalam DB transaction -> log harus ikut ter-rollback
     *    bersama data yang dicatatnya;
     *  - berjalan di console (artisan, scheduler, queue worker) atau unit
     *    test -> tidak ada "akhir response" yang bisa ditunggu;
     *  - nilai old/new tidak bisa di-encode JSON -> jatuh ke create() biasa
     *    supaya errornya sama seperti sebelumnya.
     *
     * Baca-setelah-tulis tetap konsisten: setiap query ke model ini
     * (newQuery()) otomatis mem-flush antrian lebih dulu.
     */
    public static function record(
        string $event,
        ?string $identifier = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $attributes = [
            'user_id'     => auth()->id(),
            'event'       => strtoupper($event),
            'identifier'  => $identifier,
            'description' => $description,
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
        ];

        if (! static::shouldDefer()) {
            return self::create($attributes);
        }

        try {
            $now = now()->toDateTimeString();

            $row = $attributes;
            $row['old_values'] = $oldValues === null ? null : json_encode($oldValues, JSON_THROW_ON_ERROR);
            $row['new_values'] = $newValues === null ? null : json_encode($newValues, JSON_THROW_ON_ERROR);
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        } catch (\JsonException $e) {
            return self::create($attributes);
        }

        // Daftarkan flush hanya saat antrian berubah dari kosong -> berisi
        // (bukan flag static permanen) supaya tetap benar di lingkungan
        // yang menjaga proses PHP tetap hidup antar-request (mis. Octane).
        if (empty(static::$pending)) {
            app()->terminating(fn () => static::flushPending());
        }

        static::$pending[] = $row;

        // Model belum tersimpan; return value record() memang tidak dipakai
        // pemanggil mana pun (lihat seluruh app/), hanya dipertahankan demi
        // kompatibilitas signature.
        return new self($attributes);
    }

    /**
     * Tulis semua audit log yang tertunda dengan satu bulk INSERT.
     * Aman dipanggil berkali-kali (no-op bila antrian kosong).
     */
    public static function flushPending(): void
    {
        if (empty(static::$pending)) {
            return;
        }

        // Kosongkan lebih dulu agar pemanggilan ulang (re-entrant) tidak
        // menulis baris yang sama dua kali.
        $rows = static::$pending;
        static::$pending = [];

        $model = new static;
        $table = $model->getTable();
        $db    = $model->getConnection();

        try {
            $db->table($table)->insert($rows);
        } catch (\Throwable $e) {
            // Bulk insert gagal (mis. satu baris melanggar batas kolom):
            // coba satu per satu supaya baris lain tetap tercatat.
            report($e);

            foreach ($rows as $row) {
                try {
                    $db->table($table)->insert($row);
                } catch (\Throwable $rowError) {
                    report($rowError);
                }
            }
        }
    }

    /**
     * Semua pembacaan/penulisan lewat model ini (AuditLog::query(), ::with(),
     * ::create(), dst.) memulai dari sini, jadi log yang masih tertunda di
     * request yang sama otomatis ditulis dulu -- supaya halaman yang
     * mencatat lalu langsung menampilkan log (mis. "Aktivitas Terkini")
     * tetap melihat data terbarunya.
     */
    public function newQuery()
    {
        static::flushPending();

        return parent::newQuery();
    }

    protected static function shouldDefer(): bool
    {
        return ! app()->runningInConsole()
            && ! app()->runningUnitTests()
            && DB::transactionLevel() === 0;
    }
}