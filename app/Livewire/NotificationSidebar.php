<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AuditLog;
use App\Models\RecurringTransaction;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Services\FonnteOtpService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationSidebar extends Component
{
    public string $role = 'master';
    public string $badgeText = 'Baru';
    public string $viewAllUrl = '#';

    // Kredensial (username/password baru) untuk ditampilkan lewat popup
    // <x-credentials-modal> setelah Approve permintaan reset password --
    // lihat approvePasswordResetRequest() di bawah. Sama persis dengan pola
    // App\Livewire\Master\Users\Index::$createdCredentials, TERMASUK nama
    // propertinya, karena <x-credentials-modal> hardcode nama properti ini
    // saat menutup diri (`$wire.set('createdCredentials', null)`).
    public ?array $createdCredentials = null;

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function approve($notificationId)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $notification = $user?->notifications()->find($notificationId);
        if (!$notification) return;

        // ================= PERMINTAAN RESET PASSWORD (ADMIN UNIT) =================
        // Notifikasi ini datang dari App\Livewire\Master\Settings\Index::
        // requestPasswordReset() (dipanggil lewat App\Livewire\Unit\Profile\
        // Index, lihat komentar di sana), ditandai extraData 'type' =>
        // 'password_reset_request' pada App\Notifications\SystemNotification.
        // BEDA dari alur "Konfirmasi Transaksi Berulang" di bawah (yang
        // dideteksi dari ada/tidaknya 'recurring_transaction_id'), tipe
        // notifikasi ini butuh dispatch eksplisit lewat field 'type' karena
        // datanya ('target_user_id') bisa ambigu kalau cuma dicek isEmpty.
        if (($notification->data['type'] ?? null) === 'password_reset_request') {
            $this->approvePasswordResetRequest($notification);
            $notification->markAsRead();
            return;
        }

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                // Cari ID Kategori dengan Fallback otomatis
                $categoryId = $recurring->finance_category_id 
                    ?? $recurring->category_id 
                    ?? \App\Models\FinanceCategory::where('type', $recurring->type)->value('id');

                // Cegah eksekusi jika kategori tetap tidak ditemukan
                if (!$categoryId) {
                    session()->flash('error', 'Kategori keuangan tidak ditemukan untuk transaksi ini.');
                    return;
                }

                // 1. Buat Transaksi Riil Keuangan
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

                // 2. Majukan tanggal jatuh tempo berikutnya
                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        // 3. Tandai notifikasi sebagai dibaca
        $notification->markAsRead();
    }

    /**
     * Eksekusi persetujuan permintaan reset password: generate password
     * baru, simpan (di-hash), paksa ganti password di login berikutnya
     * (must_change_password), kirim kredensial baru ke WhatsApp admin unit
     * yang bersangkutan via Fonnte, dan catat ke Audit Log. Pola generate
     * & kirimnya SENGAJA disamakan persis dengan App\Livewire\Master\Users\
     * Index::resetPassword() (reset manual dari halaman Manajemen Admin)
     * supaya perilakunya konsisten dari mana pun reset dipicu.
     */
    private function approvePasswordResetRequest($notification): void
    {
        $targetUserId = $notification->data['target_user_id'] ?? null;
        $user = $targetUserId ? User::find($targetUserId) : null;

        if (! $user) {
            session()->flash('error', 'Akun admin yang mengajukan permintaan ini sudah tidak ditemukan.');
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
            . "Segera login dan ganti password Anda. Jangan bagikan kredensial ini kepada siapapun.";

        $waSent = app(FonnteOtpService::class)->sendPlainMessage($user->phone, $waMessage);

        // Munculkan popup kredensial yang bisa disalin -- PERSIS seperti
        // popup yang muncul saat Admin Master mereset password lewat menu
        // Master > Admin (App\Livewire\Master\Users\Index::resetPassword()).
        // Sengaja TIDAK pakai session()->flash('success', ...) untuk
        // kredensialnya (password plain-text tidak boleh singgah di
        // session flash), popup ini yang jadi satu-satunya tempat admin
        // master melihat & menyalin password barunya.
        $this->createdCredentials = [
            'title'    => '🔑 Permintaan Reset Password Disetujui!',
            'name'     => $user->name,
            'username' => $user->username,
            'password' => $newPassword,
            'wa_sent'  => $waSent,
        ];
    }

    public function reject($notificationId)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $notification = $user?->notifications()->find($notificationId);
        if (!$notification) return;

        if (($notification->data['type'] ?? null) === 'password_reset_request') {
            // Tolak: TIDAK ada perubahan password apa pun -- cukup tandai
            // dibaca. Admin unit yang mengajukan bisa mengajukan ulang
            // kapan saja lewat card "Ajukan Reset Password" di profilnya.
            $targetUserName = $notification->data['target_user_name'] ?? null;
            session()->flash('message', 'Permintaan reset password'
                . ($targetUserName ? " dari '{$targetUserName}'" : '')
                . ' ditolak.');
            $notification->markAsRead();
            return;
        }

        $recurringId = $notification->data['recurring_transaction_id'] ?? null;

        if ($recurringId) {
            $recurring = RecurringTransaction::find($recurringId);

            if ($recurring) {
                // Majukan tanggal tanpa membuat transaksi riil
                $recurring->next_run_date = $this->calculateNextRunDate($recurring->next_run_date, $recurring->frequency);
                $recurring->save();
            }
        }

        $notification->markAsRead();
    }

    public function markAsRead($notificationId, $url = null)
    {
        /** @var User|null $user */
        $user = Auth::user();

        $notification = $user?->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }

        if ($url && $url !== '#') {
            return redirect()->to($url);
        }
    }

    public function markAllAsRead()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $user?->unreadNotifications->markAsRead();
    }

    private function calculateNextRunDate($currentDate, $frequency)
    {
        $date = Carbon::parse($currentDate);

        // Tampung hasil match ke variabel terlebih dahulu
        $nextDate = match ($frequency) {
            'daily'   => $date->addDay(),
            'weekly'  => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'yearly'  => $date->addYear(),
            default   => $date->addMonth(),
        };

        return $nextDate->toDateString();
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $notifications = $user
            ? $user->unreadNotifications()->latest()->take(10)->get()
            : collect();

        // Ubah 'livewire.notification-sidebar' menjadi 'components.notification-sidebar'
        return view('components.notification-sidebar', [
            'notifications' => $notifications
        ]);
    }
}