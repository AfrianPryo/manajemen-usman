<?php

namespace App\Livewire\Master\Activities;

use App\Exports\AuthLogExport;
use App\Models\AuditLog;
use App\Models\AuthLog;
use App\Models\BlockedAccess;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Menu "Keamanan" milik Master Admin (sebelumnya bernama "Aktivitas" --
 * lihat catatan penamaan di routes/web.php & config/menu.php). Tetap berisi
 * fungsi lama (Monitoring Aktivitas Login: $logs, exportLog()) DITAMBAH
 * fungsi baru untuk memblokir IP/perangkat (blockAccess()/unblockAccess()),
 * KHUSUS Master Admin.
 *
 * PENTING -- class ini diwarisi oleh App\Livewire\Unit\Activities\Index
 * (log aktivitas milik unit-admin sendiri, lihat komentar di class
 * tersebut) dan KEDUANYA merender view yang SAMA PERSIS
 * (resources/views/livewire/master/activities/index.blade.php). Supaya
 * fitur blokir ini TIDAK ikut bocor ke sisi Unit Admin:
 *
 *   1. Fitur blokir HANYA dikirim ke view lewat variabel $blockedAccesses
 *      pada render() DI SINI. Unit\Activities\Index meng-override
 *      render()-nya sendiri dan TIDAK menyertakan variabel ini, sehingga
 *      blade otomatis menyembunyikan seluruh bagian terkait cukup dengan
 *      @isset($blockedAccesses) (lihat blade-nya).
 *   2. Setiap method blokir (blockAccess/unblockAccess/openBlockModal)
 *      TETAP memanggil abort_unless(isMasterAdmin()) di baris pertama.
 *      Ini pertahanan lapis kedua: method public Livewire tetap bisa
 *      "dipanggil paksa" lewat request buatan sendiri (bypass UI) oleh
 *      siapa pun yang berhasil me-render component ini, termasuk lewat
 *      pewarisan class Unit\Activities\Index -- jangan hapus baris ini
 *      meski butir 1 di atas sudah menyembunyikan tombolnya di UI.
 */
#[Layout('components.layouts.app')]
#[Title('Monitoring Keamanan')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $eventFilter = '';

    // ================= BLOKIR IP / PERANGKAT (KHUSUS MASTER ADMIN) =================
    public bool $showBlockModal = false;
    public string $blockType = 'ip';
    public string $blockValue = '';
    public string $blockReason = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingEventFilter(): void { $this->resetPage(); }

    /**
     * Export seluruh log aktivitas login (mengikuti filter yang sedang aktif).
     */
    public function exportLog()
    {
        return Excel::download(
            new AuthLogExport([
                'search'      => $this->search,
                'eventFilter' => $this->eventFilter,
            ]),
            'log-aktivitas-login-' . now()->format('Y-m-d_His') . '.xlsx'
        );
    }

    public function openBlockModal(): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);

        $this->reset(['blockType', 'blockValue', 'blockReason']);
        $this->blockType = BlockedAccess::TYPE_IP;
        $this->resetValidation();
        $this->showBlockModal = true;
    }

    /**
     * Isi otomatis field "Nilai" dengan IP milik baris log yang diklik,
     * supaya Master Admin tidak perlu mengetik ulang alamat IP dari tabel
     * di atasnya.
     */
    public function openBlockModalFor(string $type, ?string $value): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);

        if (empty($value)) {
            return;
        }

        $this->reset(['blockType', 'blockValue', 'blockReason']);
        $this->blockType = $type === BlockedAccess::TYPE_DEVICE ? BlockedAccess::TYPE_DEVICE : BlockedAccess::TYPE_IP;
        $this->blockValue = $value;
        $this->resetValidation();
        $this->showBlockModal = true;
    }

    public function closeBlockModal(): void
    {
        $this->showBlockModal = false;
    }

    /**
     * Simpan entri blokir baru. Divalidasi manual (bukan Rule object)
     * supaya gaya pesan errornya konsisten dengan komponen lain di
     * aplikasi ini (lihat App\Livewire\Auth\Login::login()).
     */
    public function blockAccess(): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);

        $this->blockValue = trim($this->blockValue);

        $this->validate([
            'blockType'   => 'required|in:ip,device',
            'blockValue'  => 'required|string|max:255',
            'blockReason' => 'nullable|string|max:255',
        ], [], [
            'blockValue' => 'Nilai',
        ]);

        if ($this->blockType === BlockedAccess::TYPE_IP && !filter_var($this->blockValue, FILTER_VALIDATE_IP)) {
            $this->addError('blockValue', 'Format alamat IP tidak valid.');
            return;
        }

        // Jaga-jaga: cegah Master Admin memblokir IP/perangkat yang
        // SEDANG dipakainya sendiri saat ini -- tanpa ini, Master Admin
        // bisa mengunci diri sendiri dari sistem (lihat pengecekan di
        // App\Http\Middleware\EnsureUserIsActive yang langsung logout
        // paksa begitu IP/perangkat aktif terdeteksi diblokir).
        if ($this->blockType === BlockedAccess::TYPE_IP && $this->blockValue === request()->ip()) {
            $this->addError('blockValue', 'Anda tidak bisa memblokir alamat IP yang sedang Anda gunakan sendiri.');
            return;
        }
        if ($this->blockType === BlockedAccess::TYPE_DEVICE && $this->blockValue === request()->userAgent()) {
            $this->addError('blockValue', 'Anda tidak bisa memblokir perangkat yang sedang Anda gunakan sendiri.');
            return;
        }

        $existing = BlockedAccess::where('type', $this->blockType)->where('value', $this->blockValue)->first();
        if ($existing) {
            $this->addError('blockValue', 'IP/perangkat ini sudah ada dalam daftar blokir.');
            return;
        }

        $blocked = BlockedAccess::create([
            'type'       => $this->blockType,
            'value'      => $this->blockValue,
            'reason'     => $this->blockReason ?: null,
            'blocked_by' => Auth::id(),
        ]);

        AuditLog::record(
            event: 'SECURITY_BLOCK_CREATED',
            identifier: $blocked->value,
            description: sprintf(
                "Admin master memblokir %s '%s'%s.",
                $blocked->type === BlockedAccess::TYPE_IP ? 'alamat IP' : 'perangkat (User-Agent)',
                $blocked->value,
                $blocked->reason ? " dengan alasan: {$blocked->reason}" : ''
            ),
            newValues: $blocked->getAttributes()
        );

        session()->flash('message', 'Akses berhasil diblokir.');
        $this->closeBlockModal();
    }

    /**
     * Buka blokir. Baris dihapus dari `blocked_accesses` (lihat catatan
     * desain di migrasinya) -- riwayatnya tetap tersimpan lewat AuditLog.
     */
    public function unblockAccess(int $id): void
    {
        abort_unless(Auth::user()?->isMasterAdmin(), 403);

        $blocked = BlockedAccess::find($id);
        if (!$blocked) {
            return;
        }

        AuditLog::record(
            event: 'SECURITY_BLOCK_REMOVED',
            identifier: $blocked->value,
            description: sprintf(
                "Admin master membuka blokir %s '%s'.",
                $blocked->type === BlockedAccess::TYPE_IP ? 'alamat IP' : 'perangkat (User-Agent)',
                $blocked->value
            ),
            oldValues: $blocked->getAttributes()
        );

        $blocked->delete();

        session()->flash('message', 'Blokir berhasil dibuka.');
    }

    public function render()
    {
        $logs = AuthLog::with('user')
            ->when($this->search, fn ($q) => $q->where('identifier', 'like', "%{$this->search}%")
                                                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->eventFilter, fn ($q) => $q->where('event', $this->eventFilter))
            ->latest('created_at')
            ->paginate(20);

        // $blockedAccesses SENGAJA hanya diisi di sini (Master), tidak di
        // Unit\Activities\Index::render() -- lihat catatan panjang di
        // docblock class ini. Aman diisi tanpa cek isMasterAdmin() lagi
        // karena route 'master.activities.index' sendiri sudah dijaga
        // middleware 'role:master-admin' (lihat routes/web.php).
        return view('livewire.master.activities.index', [
            'logs'             => $logs,
            'blockedAccesses'  => BlockedAccess::with('blockedBy')->latest()->get(),
        ]);
    }
}
