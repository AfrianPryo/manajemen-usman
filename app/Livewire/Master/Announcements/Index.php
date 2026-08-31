<?php

namespace App\Livewire\Master\Announcements;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\FonnteOtpService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * "Pengumuman" -- Master Admin mengirim pengumuman manual ke Unit Admin
 * (mis. "libur nasional", "deadline laporan bulanan"), berbeda dari
 * notifikasi otomatis sistem (alert stok/aset habis dsb, lihat
 * App\Notifications\SystemNotification yang dipakai Master\Inventory\Index
 * & Master\Asset\Index) yang dipicu sistem sendiri, bukan ditulis manual.
 *
 * Penerima bisa dipilih: SELURUH Admin Unit aktif, atau beberapa Admin
 * Unit tertentu saja (mis. pengumuman yang hanya relevan untuk 1-2 unit).
 *
 * Infrastruktur notifikasi in-app REUSE PENUH SystemNotification yang
 * sudah ada (badge 'Pengumuman' membedakannya dari badge alert seperti
 * 'Stok Menipis' di NotificationSidebar). Selain itu, pengumuman juga bisa
 * SEKALIGUS dikirim lewat WhatsApp (Fonnte) ke nomor HP yang terdaftar
 * pada masing-masing akun admin (lihat App\Services\FonnteOtpService::
 * sendPlainMessage(), method generik non-OTP yang reuse kredensial
 * `wa_api_key` yang sama dengan alur OTP password/nomor WA).
 *
 * Yang baru dari versi sebelumnya: (1) form untuk menulis & mengirimnya,
 * (2) tabel `announcements` sebagai riwayat pengumuman yang pernah dikirim
 * (isi pesan lengkap, siapa pengirim, & berapa admin yang menerima),
 * karena `notifications` bawaan Laravel per-user & tidak praktis dipakai
 * sebagai "daftar pengumuman" tersendiri, (3) opsi target penerima
 * (semua/tertentu), dan (4) opsi kirim tambahan lewat WhatsApp Fonnte.
 */
#[Layout('components.layouts.app')]
#[Title('Pengumuman')]
class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;

    public string $title = '';
    public string $message = '';
    public string $badge = 'Pengumuman';

    // Target Penerima: 'all' = seluruh admin unit aktif, 'specific' = pilih manual
    public string $recipientType = 'all';
    /** @var array<int> */
    public array $selectedUserIds = [];

    // Kirim juga lewat WhatsApp (Fonnte) ke nomor HP admin yang terdaftar
    public bool $sendViaWhatsapp = false;

    protected function rules(): array
    {
        return [
            'title'                => 'required|string|max:150',
            'message'              => 'required|string|max:2000',
            'badge'                => 'required|string|max:50',
            'recipientType'        => 'required|in:all,specific',
            'selectedUserIds'      => 'required_if:recipientType,specific|array',
            'selectedUserIds.*'    => 'exists:users,id',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedUserIds.required_if' => 'Pilih minimal satu admin sebagai penerima pengumuman.',
        ];
    }

    public function openCreateModal(): void
    {
        $this->reset(['title', 'message', 'recipientType', 'selectedUserIds', 'sendViaWhatsapp']);
        $this->badge = 'Pengumuman';
        $this->recipientType = 'all';
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    /**
     * Semua Admin Unit yang berstatus aktif -- dipakai untuk (a) kartu
     * statistik "Admin Unit Aktif", dan (b) daftar pilihan checkbox saat
     * recipientType = 'specific'.
     */
    private function activeUnitAdmins()
    {
        return User::role('unit-admin')->active()->orderBy('name')->get();
    }

    /**
     * Target penerima aktual berdasarkan pilihan di form:
     * - 'all'      : seluruh Admin Unit aktif (mirror alur lama).
     * - 'specific' : hanya Admin Unit aktif yang dicentang manual.
     */
    private function recipients()
    {
        if ($this->recipientType === 'specific') {
            return User::role('unit-admin')->active()
                ->whereIn('id', $this->selectedUserIds)
                ->get();
        }

        return $this->activeUnitAdmins();
    }

    public function send(): void
    {
        $this->validate();

        $recipients = $this->recipients();

        if ($recipients->isEmpty()) {
            $this->addError('selectedUserIds', 'Tidak ada admin aktif yang cocok dengan pilihan penerima ini.');
            return;
        }

        // 1. Notifikasi in-app (selalu dikirim, seperti alur sebelumnya)
        foreach ($recipients as $user) {
            $user->notify(new SystemNotification(
                title: $this->title,
                message: $this->message,
                badge: $this->badge,
                actionable: false,
                url: null,
            ));
        }

        // 2. Opsional: kirim juga lewat WhatsApp (Fonnte) ke nomor HP admin
        $waSentCount = 0;
        $waSkipped = [];

        if ($this->sendViaWhatsapp) {
            $fonnte = app(FonnteOtpService::class);
            $waText = "📢 *{$this->title}*\n\n{$this->message}\n\n_Pengumuman ini dikirim otomatis oleh sistem._";

            foreach ($recipients as $user) {
                if (empty($user->phone)) {
                    $waSkipped[] = "{$user->name} (nomor HP belum terdaftar)";
                    continue;
                }

                if ($fonnte->sendPlainMessage($user->phone, $waText)) {
                    $waSentCount++;
                } else {
                    $waSkipped[] = "{$user->name} (gagal terkirim)";
                }
            }
        }

        $announcement = Announcement::create([
            'user_id'           => auth()->id(),
            'title'             => $this->title,
            'message'           => $this->message,
            'badge'             => $this->badge,
            'recipients_count'  => $recipients->count(),
        ]);

        AuditLog::record(
            event: 'ANNOUNCEMENT_SENT',
            identifier: $announcement->title,
            description: sprintf(
                "Admin master mengirim pengumuman '%s' ke %d admin unit (%s)%s.",
                $announcement->title,
                $recipients->count(),
                $this->recipientType === 'specific' ? 'dipilih manual' : 'seluruh admin unit aktif',
                $this->sendViaWhatsapp ? ", termasuk WhatsApp Fonnte ke {$waSentCount} nomor" : ''
            ),
            oldValues: null,
            newValues: array_merge($announcement->getAttributes(), [
                'recipient_type'     => $this->recipientType,
                'recipient_ids'      => $recipients->pluck('id')->all(),
                'sent_via_whatsapp'  => $this->sendViaWhatsapp,
                'wa_sent_count'      => $waSentCount,
            ])
        );

        $flashMessage = "Pengumuman berhasil dikirim ke {$recipients->count()} admin unit.";
        if ($this->sendViaWhatsapp) {
            $flashMessage .= " WhatsApp (Fonnte) terkirim ke {$waSentCount} nomor.";
            if (! empty($waSkipped)) {
                $flashMessage .= ' Tidak terkirim ke: ' . implode(', ', $waSkipped) . '.';
            }
        }

        session()->flash('message', $flashMessage);
        $this->closeModal();
    }

    public function render()
    {
        $announcements = Announcement::query()
            ->with('sender')
            ->latest()
            ->paginate(10);

        $activeUnitAdmins = $this->activeUnitAdmins();

        return view('livewire.master.announcements.index', [
            'announcements'     => $announcements,
            'activeUnitAdmins'  => $activeUnitAdmins,
            'recipientsCount'   => $activeUnitAdmins->count(),
            'targetCount'       => $this->recipients()->count(),
        ]);
    }
}
