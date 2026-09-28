<?php

namespace App\Support\Concerns;

use App\Models\AuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\RecurringTransaction;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\FonnteOtpService;
use App\Support\NotificationLink;
use Carbon\Carbon;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Aksi notifikasi yang dipakai bersama oleh App\Livewire\NotificationSidebar
 * (lonceng di header) dan App\Livewire\Master\Notifications\Index (halaman
 * penuh, diwarisi juga oleh versi Unit). Sebelumnya logika ini disalin di
 * dua tempat dan mulai berbeda perilakunya.
 *
 * Aturan yang ditegakkan di sini:
 *  - Tujuan "Lihat detail" dihitung SERVER-side dari data notifikasi
 *    (NotificationLink), bukan dikirim dari browser.
 *  - Approve/Reject bersifat "keputusan pertama menang": notifikasi yang
 *    sama dikirim ke banyak akun, jadi begitu satu akun memutuskan, salinan
 *    milik akun lain ikut ditutup. Tanpa ini dua admin yang menyetujui
 *    bisa membuat transaksi ganda / mereset password dua kali.
 *  - Notifikasi yang butuh keputusan tidak ikut tertutup oleh "Tandai
 *    semua dibaca" maupun saat tombol detail diklik.
 *
 * Properti $createdCredentials WAJIB bernama persis ini: komponen
 * <x-credentials-modal> me-reset-nya lewat $wire.set('createdCredentials', null).
 */
trait HandlesNotificationActions
{
    public ?array $createdCredentials = null;

    /**
     * Dipanggil setelah data notifikasi berubah. Default: minta lonceng di
     * header menyegarkan diri. NotificationSidebar meng-override jadi
     * no-op karena ia sendiri yang sedang di-render ulang.
     */
    protected function notificationsChanged(): void
    {
        $this->dispatch('refreshNotifications');
    }

    protected function toast(string $type, string $message): void
    {
        // now() = hanya berlaku di request ini, supaya toast tidak muncul
        // lagi di halaman berikutnya (alert.blade membaca session).
        session()->now($type === 'error' ? 'error' : 'message', $message);
    }

    private function findNotification(string $id): ?DatabaseNotification
    {
        return Auth::user()?->notifications()->find($id);
    }

    private static function isActionable(DatabaseNotification $notification): bool
    {
        return (bool) ($notification->data['actionable'] ?? false);
    }

    // =====================================================================
    // BUKA / BACA
    // =====================================================================

    public function open(string $notificationId)
    {
        $user = Auth::user();
        $notification = $this->findNotification($notificationId);

        if (! $user || ! $notification) {
            return null;
        }

        // Membuka detail tidak boleh "menghilangkan" notifikasi yang masih
        // menunggu Approve/Reject.
        if (! self::isActionable($notification)) {
            $notification->markAsRead();
        }

        $this->notificationsChanged();

        $target = NotificationLink::resolve($notification->data, $user);

        if ($target['url']) {
            return redirect()->to($target['url']);
        }

        $this->toast('error', $target['message'] ?? 'Halaman tujuan tidak tersedia.');

        return null;
    }

    public function markAsRead(string $notificationId): void
    {
        $this->findNotification($notificationId)?->markAsRead();
        $this->notificationsChanged();
    }

    public function markAllAsRead(): void
    {
        Auth::user()?->unreadNotifications()
            ->get()
            ->reject(fn ($n) => self::isActionable($n))
            ->each->markAsRead();

        $this->notificationsChanged();
    }

    // =====================================================================
    // APPROVE / REJECT
    // =====================================================================

    public function approve(string $notificationId): void
    {
        $this->decide($notificationId, approve: true);
    }

    public function reject(string $notificationId): void
    {
        $this->decide($notificationId, approve: false);
    }

    private function decide(string $notificationId, bool $approve): void
    {
        $user = Auth::user();
        $notification = $this->findNotification($notificationId);

        if (! $user || ! $notification) {
            return;
        }

        if ($notification->read_at) {
            $this->toast('error', 'Notifikasi ini sudah diproses.');
            $this->notificationsChanged();

            return;
        }

        if (($notification->data['type'] ?? null) === 'password_reset_request') {
            $approve
                ? $this->approvePasswordReset($notification, $user)
                : $this->rejectPasswordReset($notification);
        } elseif (! empty($notification->data['recurring_transaction_id'])) {
            $this->decideRecurring($notification, $user, $approve);
        } else {
            $notification->markAsRead();
        }

        $this->notificationsChanged();
    }

    // ---------------------------------------------------------------------
    // Transaksi berulang
    // ---------------------------------------------------------------------

    private function decideRecurring(DatabaseNotification $notification, User $user, bool $approve): void
    {
        $recurringId = (int) $notification->data['recurring_transaction_id'];

        $message = DB::transaction(function () use ($notification, $user, $approve, $recurringId): array {
            $recurring = RecurringTransaction::whereKey($recurringId)->lockForUpdate()->first();

            // Baca ulang setelah lock didapat: akun lain mungkin baru saja memutuskan.
            $notification->refresh();
            if ($notification->read_at) {
                return ['error', 'Notifikasi ini sudah diproses.'];
            }

            if (! $recurring) {
                $notification->markAsRead();

                return ['error', 'Transaksi berulang ini sudah tidak ditemukan.'];
            }

            if (! $user->isMasterAdmin() && (int) $recurring->unit_id !== (int) $user->unit_id) {
                return ['error', 'Anda tidak memiliki akses ke transaksi berulang unit lain.'];
            }

            // Sudah tidak jatuh tempo (diputuskan akun lain / jadwal diubah).
            if ($recurring->status !== 'active'
                || Carbon::parse($recurring->next_run_date)->toDateString() > now()->toDateString()
            ) {
                $this->closeSiblings('recurring_transaction_id', $recurring->id);
                $notification->markAsRead();

                return ['error', 'Transaksi berulang ini sudah diproses atau tidak lagi jatuh tempo.'];
            }

            if ($approve) {
                $categoryId = $recurring->finance_category_id
                    ?? $recurring->category_id
                    ?? FinanceCategory::where('type', $recurring->type)->value('id');

                if (! $categoryId) {
                    return ['error', 'Kategori keuangan tidak ditemukan untuk transaksi ini.'];
                }

                FinanceTransaction::create([
                    'unit_id'             => $recurring->unit_id,
                    'finance_category_id' => $categoryId,
                    'user_id'             => Auth::id() ?? 1,
                    'reference_no'        => 'TRX-REC-' . time() . '-' . $recurring->id,
                    'type'                => $recurring->type,
                    'status'              => 'completed',
                    'amount'              => $recurring->amount,
                    'description'         => "{$recurring->title} (Disetujui dari Transaksi Berulang)",
                    'transaction_date'    => now(),
                ]);
            }

            // Majukan jadwal berikutnya (dengan atau tanpa transaksi riil).
            $recurring->next_run_date = $this->nextRunDate($recurring->next_run_date, $recurring->frequency);
            $recurring->save();

            $this->closeSiblings('recurring_transaction_id', $recurring->id);
            $notification->markAsRead();

            return $approve
                ? ['message', "Transaksi '{$recurring->title}' disetujui dan dicatat ke Transaksi Keuangan."]
                : ['message', "Transaksi '{$recurring->title}' ditolak, jadwal dimajukan tanpa membuat transaksi."];
        });

        $this->toast(...$message);
    }

    // ---------------------------------------------------------------------
    // Permintaan reset password (Admin Unit -> Admin Master)
    // ---------------------------------------------------------------------

    private function approvePasswordReset(DatabaseNotification $notification, User $approver): void
    {
        if (! $approver->isMasterAdmin()) {
            $this->toast('error', 'Hanya Admin Master yang dapat menyetujui permintaan reset password.');

            return;
        }

        $targetUserId = $notification->data['target_user_id'] ?? null;
        $user = $targetUserId ? User::find($targetUserId) : null;

        if (! $user) {
            $notification->markAsRead();
            $this->toast('error', 'Akun admin yang mengajukan permintaan ini sudah tidak ditemukan.');

            return;
        }

        $newPassword = Str::random(8);
        $user->password = Hash::make($newPassword);
        $user->must_change_password = true;
        $user->save();

        AuditLog::record(
            event: 'ADMIN_PASSWORD_RESET',
            identifier: $user->username,
            description: "Admin master menyetujui permintaan reset password dari '{$user->username}' via notifikasi.",
            oldValues: null,
            newValues: ['must_change_password' => true]
        );

        $waMessage = "🔑 *Password Direset*\n\n"
            . "Halo {$user->name}, permintaan reset password Anda telah disetujui oleh Master Admin.\n\n"
            . "Username: *{$user->username}*\n"
            . "Password baru: *{$newPassword}*\n\n"
            . 'Segera login dan ganti password Anda. Jangan bagikan kredensial ini kepada siapapun.';

        $waSent = app(FonnteOtpService::class)
            ->sendPlainMessageAsync($user->phone, $waMessage, FonnteOtpService::CATEGORY_CREDENTIALS);

        // Password plain-text sengaja TIDAK lewat session flash; popup ini
        // satu-satunya tempat admin master melihat & menyalinnya.
        $this->createdCredentials = [
            'title'    => '🔑 Permintaan Reset Password Disetujui!',
            'name'     => $user->name,
            'username' => $user->username,
            'password' => $newPassword,
            'wa_sent'  => $waSent,
        ];

        // Salinan permintaan yang sama di akun master lain ditutup, supaya
        // tidak ada yang mereset ulang dan membatalkan password yang baru dikirim.
        $this->closeSiblings('target_user_id', $user->id);
        $notification->markAsRead();
    }

    private function rejectPasswordReset(DatabaseNotification $notification): void
    {
        // Tolak: tidak ada perubahan password. Admin unit bisa mengajukan
        // ulang kapan saja lewat profilnya.
        $name = $notification->data['target_user_name'] ?? null;

        $this->toast('message', 'Permintaan reset password' . ($name ? " dari '{$name}'" : '') . ' ditolak.');
        $notification->markAsRead();
    }

    // ---------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------

    /**
     * Tutup salinan notifikasi yang masih menunggu keputusan (actionable &
     * belum dibaca) milik SEMUA akun untuk item yang sama.
     */
    private function closeSiblings(string $key, int|string $value): void
    {
        DatabaseNotification::query()
            ->where('notifiable_type', User::class)
            ->where('type', SystemNotification::class)
            ->whereNull('read_at')
            ->where("data->{$key}", $value)
            ->get()
            ->filter(fn ($n) => self::isActionable($n))
            ->each->markAsRead();
    }

    private function nextRunDate($currentDate, $frequency): string
    {
        $date = Carbon::parse($currentDate);

        return (match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'yearly'  => $date->addYear(),
            default   => $date->addMonth(),
        })->toDateString();
    }
}
