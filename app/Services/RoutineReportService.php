<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ServiceOrder;
use App\Models\Setting;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Laporan Rutin Otomatis lewat WhatsApp (Fonnte) ke seluruh Admin Master
 * aktif -- merangkum aspek-aspek penting sistem (keuangan, unit usaha,
 * admin, stok, pembelian, pesanan layanan, aset, pelanggan) sesuai
 * kategori yang dipilih admin di Pengaturan > Fitur & Modul.
 *
 * Dipanggil oleh App\Console\Commands\SendRoutineReport lewat scheduler
 * (lihat routes/console.php) -- BUKAN dikirim real-time per kejadian
 * seperti pesan WA lain di FonnteOtpService, melainkan ringkasan berkala
 * (harian/mingguan/bulanan) sesuai konfigurasi.
 *
 * Pola due-check SENGAJA "at-least" (jam sekarang >= jam terjadwal) +
 * penanda periode terakhir terkirim (report_routine_last_period_key),
 * bukan exact-match menit, supaya tahan kalau scheduler/queue sempat
 * berhenti pas jam terjadwal -- command tetap akan mengirim begitu
 * berjalan lagi, tanpa mengirim dobel untuk periode yang sama.
 */
class RoutineReportService
{
    /**
     * Kategori bagian laporan yang bisa dimatikan/dinyalakan admin satu
     * per satu lewat Pengaturan > Fitur & Modul > Laporan Rutin Otomatis.
     */
    public const SECTIONS = [
        'finance'         => 'Ringkasan Keuangan (Omzet & Pengeluaran)',
        'transactions'    => 'Aktivitas Transaksi',
        'units'           => 'Unit Usaha',
        'admins'          => 'Admin & Pengguna',
        'stock'           => 'Stok Produk Menipis',
        'purchase_orders' => 'Pembelian (Purchase Order)',
        'service_orders'  => 'Pesanan Layanan',
        'assets'          => 'Aset (Perbaikan/Rusak)',
        'customers'       => 'Pelanggan Baru',
    ];

    protected const FREQUENCY_LABELS = [
        'daily'   => 'Harian',
        'weekly'  => 'Mingguan',
        'monthly' => 'Bulanan',
    ];

    /**
     * Entry point utama, dipanggil command terjadwal. Tidak melakukan
     * apa-apa kalau belum waktunya terkirim (lihat isDue()).
     */
    public function process(): void
    {
        if (! $this->isDue()) {
            return;
        }

        $now        = Carbon::now();
        $frequency  = Setting::get('report_routine_frequency', 'daily');
        $periodKey  = $this->currentPeriodKey($frequency, $now);
        $sections   = $this->enabledSections();
        [$start, $end] = $this->periodRange($frequency, $now);

        $message = $this->buildMessage($frequency, $start, $end, $sections);

        $recipients = User::role('master-admin')
            ->active()
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->get();

        if ($recipients->isEmpty()) {
            Log::warning('RoutineReportService: tidak ada Admin Master aktif dengan nomor WhatsApp terdaftar, laporan rutin dilewati untuk periode ini.');
        } else {
            $fonnte = app(FonnteOtpService::class);
            foreach ($recipients as $admin) {
                // Kategori 'routine_report' sengaja TIDAK didaftarkan di
                // FonnteOtpService::channelEnabled() sebagai saklar
                // granular tersendiri -- fitur ini sudah punya saklar
                // sendiri (report_routine_enabled, dicek di isDue()).
                // channelEnabled() tetap menahannya kalau saklar utama
                // 'enable_wa_notifications' dimatikan.
                $fonnte->sendPlainMessageAsync($admin->phone, $message, 'routine_report');
            }
        }

        // Tandai periode ini sudah diproses supaya scheduler (jalan tiap
        // 15 menit) tidak mengirim ulang untuk periode yang sama, baik
        // berhasil ada penerima maupun tidak.
        Setting::set('report_routine_last_period_key', $periodKey);
        Setting::set('report_routine_last_sent_at', $now->toDateTimeString());

        AuditLog::record(
            event: 'ROUTINE_REPORT_SENT',
            identifier: null,
            description: sprintf(
                'Laporan rutin otomatis (%s) dikirim ke %d Admin Master via WhatsApp.',
                self::FREQUENCY_LABELS[$frequency] ?? $frequency,
                $recipients->count(),
            ),
            newValues: [
                'frequency'  => $frequency,
                'period'     => $periodKey,
                'sections'   => $sections,
                'recipients' => $recipients->count(),
            ],
        );
    }

    /**
     * Apakah laporan rutin sudah waktunya dikirim saat ini.
     */
    public function isDue(): bool
    {
        if (! (bool) Setting::get('report_routine_enabled', false)) {
            return false;
        }

        // Saklar utama notifikasi WA -- kalau dimatikan, semua pesan WA
        // non-OTP (termasuk laporan rutin ini) memang tidak boleh terkirim.
        if (! (bool) Setting::get('enable_wa_notifications', false)) {
            return false;
        }

        if (empty(Setting::get('wa_api_key'))) {
            return false;
        }

        $now       = Carbon::now();
        $frequency = Setting::get('report_routine_frequency', 'daily');

        if ($frequency === 'weekly') {
            $dayOfWeek = (int) Setting::get('report_routine_day_of_week', 1);
            if ($now->dayOfWeek !== $dayOfWeek) {
                return false;
            }
        } elseif ($frequency === 'monthly') {
            $dayOfMonth = (int) Setting::get('report_routine_day_of_month', 1);
            // Clamp ke hari terakhir bulan berjalan supaya tanggal seperti
            // 31 tetap bisa terkirim di bulan yang cuma 28/29/30 hari.
            $targetDay = min($dayOfMonth, $now->daysInMonth);
            if ($now->day !== $targetDay) {
                return false;
            }
        }

        $time = Setting::get('report_routine_time', '07:00');
        [$hour, $minute] = array_pad(array_map('intval', explode(':', $time)), 2, 0);
        $scheduledAt = $now->copy()->setTime($hour, $minute, 0);

        if ($now->lt($scheduledAt)) {
            return false;
        }

        $periodKey = $this->currentPeriodKey($frequency, $now);

        return Setting::get('report_routine_last_period_key') !== $periodKey;
    }

    /**
     * Kunci unik periode berjalan sesuai frekuensi -- dipakai untuk
     * mencegah pengiriman dobel dalam periode yang sama.
     */
    protected function currentPeriodKey(string $frequency, Carbon $now): string
    {
        return match ($frequency) {
            'weekly'  => $now->format('o-\WW'),
            'monthly' => $now->format('Y-m'),
            default   => $now->format('Y-m-d'),
        };
    }

    /**
     * Rentang tanggal data yang dirangkum -- selalu periode yang BARU
     * SAJA selesai (kemarin / minggu lalu / bulan lalu), bukan
     * "sampai hari ini", supaya angkanya sudah final saat laporan
     * dikirim, apapun jam pengiriman yang dikonfigurasi.
     */
    protected function periodRange(string $frequency, Carbon $now): array
    {
        return match ($frequency) {
            'weekly' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'monthly' => [
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ],
            default => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
        };
    }

    /**
     * Daftar kategori laporan yang aktif sesuai pengaturan admin.
     * Default: SEMUA kategori aktif (perilaku aman kalau setting belum
     * pernah disimpan sama sekali).
     */
    protected function enabledSections(): array
    {
        $raw = Setting::get('report_routine_sections');
        $decoded = $raw ? json_decode($raw, true) : null;

        if (! is_array($decoded) || empty($decoded)) {
            return array_keys(self::SECTIONS);
        }

        return array_values(array_intersect($decoded, array_keys(self::SECTIONS)));
    }

    /**
     * Susun teks pesan WhatsApp final dari seluruh bagian yang aktif.
     */
    protected function buildMessage(string $frequency, Carbon $start, Carbon $end, array $sections): string
    {
        $frequencyLabel = self::FREQUENCY_LABELS[$frequency] ?? $frequency;
        $periodLabel = $start->isSameDay($end)
            ? $start->translatedFormat('d M Y')
            : $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');

        $lines = [
            "📋 *Laporan Rutin Otomatis — {$frequencyLabel}*",
            "Periode: {$periodLabel}",
            '',
        ];

        foreach ($sections as $section) {
            $body = match ($section) {
                'finance'         => $this->sectionFinance($start, $end),
                'transactions'    => $this->sectionTransactions($start, $end),
                'units'           => $this->sectionUnits(),
                'admins'          => $this->sectionAdmins(),
                'stock'           => $this->sectionStock(),
                'purchase_orders' => $this->sectionPurchaseOrders($start, $end),
                'service_orders'  => $this->sectionServiceOrders($start, $end),
                'assets'          => $this->sectionAssets(),
                'customers'       => $this->sectionCustomers($start, $end),
                default           => null,
            };

            if ($body) {
                $lines[] = $body;
                $lines[] = '';
            }
        }

        $lines[] = '_Dikirim otomatis oleh sistem, ' . Carbon::now()->translatedFormat('d M Y H:i') . '._';

        return implode("\n", $lines);
    }

    protected function rupiah(float|int $amount): string
    {
        return 'Rp ' . number_format((float) $amount, 0, ',', '.');
    }

    protected function sectionFinance(Carbon $start, Carbon $end): string
    {
        $base = FinanceTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'completed');

        $income  = (clone $base)->where('type', 'income')->sum('amount');
        $expense = (clone $base)->where('type', 'expense')->sum('amount');
        $net     = $income - $expense;

        return "💰 *Ringkasan Keuangan*\n"
            . "Pemasukan: {$this->rupiah($income)}\n"
            . "Pengeluaran: {$this->rupiah($expense)}\n"
            . 'Laba/Rugi Bersih: ' . $this->rupiah($net);
    }

    protected function sectionTransactions(Carbon $start, Carbon $end): string
    {
        $base = FinanceTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->where('status', 'completed');

        $count = (clone $base)->count();
        $total = (clone $base)->sum('amount');
        $avg   = $count > 0 ? $total / $count : 0;

        return "📊 *Aktivitas Transaksi*\n"
            . "Total transaksi selesai: {$count}\n"
            . 'Rata-rata nilai per transaksi: ' . $this->rupiah($avg);
    }

    protected function sectionUnits(): string
    {
        $total  = Unit::count();
        $active = Unit::where('is_active', true)->count();

        return "🏢 *Unit Usaha*\n"
            . "Total unit: {$total} ({$active} aktif, " . ($total - $active) . ' nonaktif)';
    }

    protected function sectionAdmins(): string
    {
        $master = User::role('master-admin')->count();
        $unit   = User::role('unit-admin')->count();

        return "👥 *Admin & Pengguna*\n"
            . "Admin Master: {$master}\n"
            . "Admin Unit: {$unit}";
    }

    protected function sectionStock(): string
    {
        $lowStock = Product::whereColumn('stock', '<=', 'min_stock')
            ->with('unit')
            ->orderBy('stock')
            ->limit(5)
            ->get();

        $totalLowStock = Product::whereColumn('stock', '<=', 'min_stock')->count();

        $text = "📦 *Stok Produk Menipis*\nTotal produk stok menipis: {$totalLowStock}";

        if ($totalLowStock > 0) {
            foreach ($lowStock as $product) {
                $unitName = $product->unit?->name ?? '-';
                $text .= "\n- {$product->name} ({$unitName}): {$product->stock}/{$product->min_stock}";
            }
            if ($totalLowStock > $lowStock->count()) {
                $text .= "\n… dan " . ($totalLowStock - $lowStock->count()) . ' produk lainnya.';
            }
        }

        return $text;
    }

    protected function sectionPurchaseOrders(Carbon $start, Carbon $end): string
    {
        $base = PurchaseOrder::whereBetween('created_at', [$start, $end]);

        $completed      = (clone $base)->where('status', 'completed')->count();
        $completedTotal = (clone $base)->where('status', 'completed')->sum('total_amount');
        $cancelled      = (clone $base)->where('status', 'cancelled')->count();

        return "🛒 *Pembelian (Purchase Order)*\n"
            . "PO selesai: {$completed} (total {$this->rupiah($completedTotal)})\n"
            . "PO dibatalkan: {$cancelled}";
    }

    protected function sectionServiceOrders(Carbon $start, Carbon $end): string
    {
        $counts = ServiceOrder::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return "🔧 *Pesanan Layanan*\n"
            . 'Pending: ' . ($counts['pending'] ?? 0) . "\n"
            . 'Diproses: ' . ($counts['in_progress'] ?? 0) . "\n"
            . 'Selesai: ' . ($counts['completed'] ?? 0) . "\n"
            . 'Dibatalkan: ' . ($counts['cancelled'] ?? 0);
    }

    protected function sectionAssets(): string
    {
        $maintenance = Asset::where('status', 'maintenance')->count();
        $damaged     = Asset::where('condition', 'damaged')->count();

        return "🧰 *Aset*\n"
            . "Dalam perbaikan (maintenance): {$maintenance}\n"
            . "Kondisi rusak: {$damaged}";
    }

    protected function sectionCustomers(Carbon $start, Carbon $end): string
    {
        $newCustomers   = Customer::whereBetween('created_at', [$start, $end])->count();
        $totalCustomers = Customer::count();

        return "🧑‍🤝‍🧑 *Pelanggan*\n"
            . "Pelanggan baru periode ini: {$newCustomers}\n"
            . "Total pelanggan terdaftar: {$totalCustomers}";
    }
}
