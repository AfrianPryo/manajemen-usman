<?php

namespace App\Support\Concerns;

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

/**
 * Helper untuk sinkronisasi notifikasi alert (stok produk, kondisi aset, status
 * transaksi pending) secara efisien dari sisi query database.
 *
 * LATAR BELAKANG:
 * Sebelumnya, setiap komponen melakukan `foreach (User::all() as $user)` untuk
 * SETIAP item (produk/aset/transaksi) yang diperiksa, menghasilkan query
 * `exists()` / `update()` sebanyak (jumlah_item x jumlah_user) pada setiap
 * page load. Trait ini menggantinya dengan pola batch:
 *
 *   1. ID user diambil SEKALI per request (bukan per item) dan di-cache.
 *   2. Untuk "fire" (mengaktifkan alert): 1 query untuk melihat user mana saja
 *      yang SUDAH punya notifikasi pending untuk item+tipe tsb, lalu hanya user
 *      yang belum punya yang di-`notify()` (insert notifikasi baru).
 *   3. Untuk "clear" (menonaktifkan alert): 1 query UPDATE massal, bukan
 *      di-loop per user.
 *
 * PERILAKU TIDAK BERUBAH: siapa yang dinotifikasi, kapan sebuah alert dianggap
 * aktif/tidak, dan isi notifikasinya tetap identik dengan implementasi semula.
 * Yang berubah hanya JUMLAH QUERY yang dijalankan untuk mencapai hasil yang sama.
 */
trait SyncsAlertNotifications
{
    /** @var Collection<int, int>|null Cache id semua user untuk request/lifecycle ini. */
    private ?Collection $alertUserIdsCache = null;

    /**
     * Ambil id semua user yang relevan untuk menerima alert.
     * Di-cache supaya hanya dijalankan sekali walau dipanggil untuk banyak item.
     */
    private function alertUserIds(): Collection
    {
        if ($this->alertUserIdsCache === null) {
            $this->alertUserIdsCache = User::query()->pluck('id');
        }

        return $this->alertUserIdsCache;
    }

    /**
     * Cari id item (product_id/asset_id/transaction_id, dst) yang saat ini
     * memiliki notifikasi PENDING (belum dibaca) untuk salah satu tipe alert
     * yang diberikan. Dipakai supaya proses sync tidak perlu memindai SELURUH
     * tabel item, cukup item yang sedang "alert-worthy" ATAU item yang masih
     * punya notifikasi aktif yang mungkin perlu dibersihkan.
     *
     * @param  string[]  $typeValues
     * @return Collection<int, int>
     */
    private function idsWithPendingAlerts(string $idField, string $typeField, array $typeValues): Collection
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('type', SystemNotification::class)
            ->whereNull('read_at')
            ->where(function ($query) use ($typeField, $typeValues) {
                foreach ($typeValues as $index => $value) {
                    $index === 0
                        ? $query->where("data->{$typeField}", $value)
                        : $query->orWhere("data->{$typeField}", $value);
                }
            })
            ->get(['data'])
            ->map(function ($row) use ($idField) {
                $decoded = json_decode($row->data, true);

                return isset($decoded[$idField]) ? (int) $decoded[$idField] : null;
            })
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Pastikan setiap user memiliki notifikasi pending untuk kombinasi
     * item+tipe alert ini. Hanya user yang BELUM punya notifikasi pending
     * yang akan dikirimi notifikasi baru (bukan seluruh user di-loop dengan
     * query per user seperti sebelumnya).
     */
    private function batchFireAlert(
        string $idField,
        int $idValue,
        string $typeField,
        string $typeValue,
        string $title,
        string $message,
        string $badge,
        array $extraData
    ): void {
        $userIds = $this->alertUserIds();

        if ($userIds->isEmpty()) {
            return;
        }

        $alreadyNotifiedUserIds = DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('type', SystemNotification::class)
            ->whereNull('read_at')
            ->where("data->{$idField}", $idValue)
            ->where("data->{$typeField}", $typeValue)
            ->pluck('notifiable_id')
            ->map(fn ($id) => (int) $id);

        $missingUserIds = $userIds->diff($alreadyNotifiedUserIds);

        if ($missingUserIds->isEmpty()) {
            return;
        }

        User::query()->whereIn('id', $missingUserIds)->get()->each(function (User $user) use ($title, $message, $badge, $extraData) {
            $user->notify(new SystemNotification(
                title: $title,
                message: $message,
                badge: $badge,
                actionable: false,
                url: url()->current(),
                extraData: $extraData,
            ));
        });
    }

    /**
     * Tandai semua notifikasi pending untuk kombinasi item+tipe alert ini
     * sebagai sudah dibaca, dalam SATU query UPDATE massal (bukan di-loop
     * per user seperti sebelumnya).
     */
    private function batchClearAlert(string $idField, int $idValue, string $typeField, string $typeValue): void
    {
        DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('type', SystemNotification::class)
            ->whereNull('read_at')
            ->where("data->{$idField}", $idValue)
            ->where("data->{$typeField}", $typeValue)
            ->update(['read_at' => now()]);
    }
}
