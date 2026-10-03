<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Notifications\SystemNotification;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Satu-satunya sumber kebenaran untuk memproses Transaksi Berulang yang
 * sudah jatuh tempo. Dipakai bersama oleh:
 *   - App\Console\Commands\ProcessRecurringTransactions (command terjadwal
 *     `recurring:process`)
 *   - App\Livewire\Master\RecurringTransaction\Index::mount() (pengecekan
 *     saat halaman Manajemen Transaksi Berulang dibuka)
 *
 * LATAR BELAKANG PENGGABUNGAN:
 * Sebelumnya logika ini ada di DUA tempat terpisah dengan hasil yang
 * SEDIKIT BERBEDA -- versi command lama tidak mengisi finance_category_id/
 * status/reference_no, tidak mencatat AuditLog, dan tidak mengirim
 * notifikasi "diproses otomatis" untuk item auto-approve (hanya versi di
 * halaman Livewire yang melakukan semua itu). Karena keduanya bisa
 * berjalan untuk item yang sama (command terjadwal ATAU halaman dibuka
 * admin, mana pun lebih dulu), hasilnya bisa tidak konsisten tergantung
 * jalur mana yang mengeksekusi lebih dulu. Kedua tempat itu juga sama-sama
 * N+1: `User::all()` dipanggil ulang untuk SETIAP item, dan status
 * "notifikasi pending" dicek dengan query `exists()` per pasangan
 * (user, item).
 *
 * Kelas ini menyatukan jadi SATU implementasi (memakai logika versi yang
 * lebih lengkap dari halaman Livewire, karena itu yang sudah mengisi audit
 * log & kategori dengan benar), dan sekaligus memperbaiki N+1-nya:
 * `User::all()` dan pengecekan notifikasi pending kini masing-masing hanya
 * dijalankan SEKALI per batch, bukan per item.
 *
 * PROTEKSI RACE CONDITION:
 * Karena logika ini kini bisa dipicu dari dua jalur berbeda (command &
 * halaman) yang secara teori bisa berjalan nyaris bersamaan, setiap item
 * auto-approve diproses di dalam transaksi DB dengan row lock
 * (`lockForUpdate`) dan re-check status/next_run_date setelah lock
 * didapat -- supaya item yang sama tidak pernah diproses dua kali menjadi
 * dua FinanceTransaction terpisah.
 */
class RecurringTransactionService
{
    /**
     * Proses seluruh transaksi berulang aktif yang sudah jatuh tempo hari
     * ini: item auto-approve langsung dibuatkan transaksi resmi, item
     * konfirmasi manual dikirimi notifikasi interaktif.
     */
    public function processDueTransactions(): void
    {
        $today = now()->toDateString();

        $dueTransactions = RecurringTransaction::where('status', 'active')
            ->whereDate('next_run_date', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')
                      ->orWhereDate('end_date', '>=', $today);
            })
            ->with('unit:id,slug')
            ->get();

        if ($dueTransactions->isEmpty()) {
            return;
        }

        $manualItems = $dueTransactions->reject(fn ($item) => $item->auto_approve);

        // -------------------------------------------------------------
        // BATCH PRELOAD -- dijalankan SEKALI untuk seluruh batch item due,
        // bukan di dalam loop per item seperti implementasi lama:
        // -------------------------------------------------------------
        // Penerima notifikasi: Master Admin aktif + Admin Unit aktif milik
        // unit terkait saja (dulu User::all() -> semua akun, termasuk unit
        // lain & akun nonaktif, ikut menerima judul & nominal transaksi).
        $masterAdmins = User::query()->where('is_active', true)->role('master-admin')->get();
        $unitAdminsByUnit = User::query()
            ->where('is_active', true)
            ->whereIn('unit_id', $dueTransactions->pluck('unit_id')->filter()->unique()->all())
            ->role('unit-admin')
            ->get()
            ->groupBy('unit_id');
        $pendingConfirmationMap = $this->pendingConfirmationMap($manualItems);

        foreach ($dueTransactions as $item) {
            $targetUsers = $masterAdmins->merge($unitAdminsByUnit->get($item->unit_id, collect()))->unique('id');

            if ($item->auto_approve) {
                $this->processAutoApprove($item, $targetUsers);
            } else {
                $this->notifyManualConfirmation(
                    $item,
                    $targetUsers,
                    $pendingConfirmationMap[$item->id] ?? []
                );
            }
        }
    }

    /**
     * Proses satu item auto-approve: buat FinanceTransaction resmi, catat
     * AuditLog, kirim notifikasi "diproses otomatis" ke semua user, lalu
     * majukan next_run_date. Dibungkus row lock supaya aman dari eksekusi
     * ganda (lihat catatan race condition di atas kelas).
     */
    private function processAutoApprove(RecurringTransaction $item, Collection $targetUsers): void
    {
        DB::transaction(function () use ($item, $targetUsers) {
            $locked = RecurringTransaction::whereKey($item->id)->lockForUpdate()->first();

            if (! $locked || $locked->status !== 'active') {
                return;
            }

            // Cari ID kategori fallback jika data lama belum terisi
            $categoryId = $locked->finance_category_id
                ?? FinanceCategory::where('type', $locked->type)
                    ->forUnit($locked->unit_id)
                    ->value('id');

            if (! $categoryId) {
                return;
            }

            $today = now()->toDateString();
            $endDate = $locked->end_date ? Carbon::parse($locked->end_date)->toDateString() : null;

            // Proses SETIAP periode yang sudah jatuh tempo sampai terkejar
            // (mis. scheduler sempat mati beberapa hari). Batas 62 periode
            // per eksekusi sebagai pengaman dari loop tak berujung.
            for ($i = 0; $i < 62; $i++) {
                // Bandingkan sebagai TANGGAL (bukan Carbon vs string, yang
                // membuat item jatuh tempo hari ini dianggap belum jatuh tempo).
                $runDate = Carbon::parse($locked->next_run_date)->toDateString();

                if ($runDate > $today || ($endDate !== null && $runDate > $endDate)) {
                    break;
                }

                $trx = FinanceTransaction::create([
                    'unit_id'             => $locked->unit_id,
                    'finance_category_id' => $categoryId,
                    // null bila dijalankan scheduler (bukan hard-coded user 1)
                    'user_id'             => Auth::id(),
                    'reference_no'        => 'TRX-REC-' . time() . '-' . $locked->id . ($i > 0 ? "-{$i}" : ''),
                    'type'                => $locked->type,
                    'status'              => 'completed',
                    'amount'              => $locked->amount,
                    'description'         => $locked->title . ' (Otomatis dibuat dari Transaksi Berulang)',
                    'transaction_date'    => $runDate === $today ? now() : Carbon::parse($runDate)->startOfDay(),
                ]);

                AuditLog::record(
                    'RECURRING_TRANSACTION_AUTO_RUN',
                    $locked->title,
                    "Transaksi otomatis dibuat dari transaksi berulang '{$locked->title}' sejumlah Rp " . number_format($locked->amount, 0, ',', '.') . ".",
                    null,
                    $trx->toArray()
                );

                foreach ($targetUsers as $user) {
                    $user->notify(new SystemNotification(
                        title: 'Transaksi Berulang Diproses Otomatis',
                        message: "Transaksi '{$locked->title}' (Rp " . number_format($locked->amount, 0, ',', '.') . ") telah dibuat otomatis ke Transaksi Keuangan.",
                        badge: 'Otomatis',
                        actionable: false,
                        url: $this->urlFor($user, $item),
                        extraData: [
                            'recurring_transaction_id' => $locked->id,
                        ]
                    ));
                }

                $locked->next_run_date = $this->calculateNextRunDate($locked->next_run_date, $locked->frequency);
            }

            $locked->save();
        });
    }

    /**
     * Tautan notifikasi sesuai role penerima (Master Admin -> halaman master,
     * Admin Unit -> halaman unitnya sendiri) agar tidak berujung 403.
     */
    private function urlFor(User $user, RecurringTransaction $item): string
    {
        if (! $user->isMasterAdmin() && $item->unit?->slug) {
            return route('unit.recurring-transactions.index', $item->unit->slug);
        }

        return route('master.recurring-transactions.index');
    }

    /**
     * Kirim notifikasi konfirmasi ke user yang BELUM punya notifikasi
     * pending untuk item ini -- daftar user yang sudah dinotifikasi
     * ($alreadyNotifiedUserIds) datang dari map yang sudah dihitung SEKALI
     * untuk seluruh batch di pendingConfirmationMap(), bukan query exists()
     * per pasangan (user, item) seperti implementasi lama.
     */
    private function notifyManualConfirmation(RecurringTransaction $item, Collection $targetUsers, array $alreadyNotifiedUserIds): void
    {
        foreach ($targetUsers as $user) {
            if (isset($alreadyNotifiedUserIds[$user->id])) {
                continue;
            }

            $user->notify(new SystemNotification(
                title: 'Konfirmasi Transaksi Berulang',
                message: "Transaksi '{$item->title}' (Rp " . number_format($item->amount, 0, ',', '.') . ") telah jatuh tempo dan butuh konfirmasi.",
                badge: 'Jatuh Tempo',
                actionable: true,
                url: $this->urlFor($user, $item),
                extraData: [
                    'recurring_transaction_id' => $item->id,
                ]
            ));
        }
    }

    /**
     * Ambil peta [recurring_transaction_id => [user_id => true]] untuk user
     * yang SUDAH punya notifikasi pending (belum dibaca) terkait item
     * manual tersebut, dalam SATU query untuk seluruh item manual sekaligus.
     *
     * @param  Collection<int, RecurringTransaction>  $manualItems
     * @return array<int, array<int, bool>>
     */
    private function pendingConfirmationMap(Collection $manualItems): array
    {
        if ($manualItems->isEmpty()) {
            return [];
        }

        $itemIds = $manualItems->pluck('id')->all();

        return DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('type', SystemNotification::class)
            ->whereNull('read_at')
            ->where(function ($query) use ($itemIds) {
                foreach ($itemIds as $index => $id) {
                    $index === 0
                        ? $query->where('data->recurring_transaction_id', $id)
                        : $query->orWhere('data->recurring_transaction_id', $id);
                }
            })
            ->get(['notifiable_id', 'data'])
            ->reduce(function (array $map, $row) {
                // $row->data sudah otomatis di-cast ke array oleh model
                // DatabaseNotification bawaan Laravel, jadi tidak perlu
                // (dan tidak boleh) di-json_decode() lagi -- lihat catatan
                // yang sama di SyncsAlertNotifications::idsWithPendingAlerts().
                $decoded = $row->data;
                $itemId = $decoded['recurring_transaction_id'] ?? null;

                if ($itemId !== null) {
                    $map[$itemId][(int) $row->notifiable_id] = true;
                }

                return $map;
            }, []);
    }

    private function calculateNextRunDate($currentDate, $frequency): string
    {
        $date = Carbon::parse($currentDate);

        return (match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonthNoOverflow(),
            'yearly'  => $date->addYearNoOverflow(),
            default   => $date->addMonthNoOverflow(),
        })->toDateString();
    }
}