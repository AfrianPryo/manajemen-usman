<?php

namespace App\Livewire\Unit;

use App\Exports\Dashboard\Unit\UnitDashboardExport;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\FinanceTransaction;
use App\Models\Product;
use App\Models\RecurringTransaction;
use App\Models\ServiceOrder;
use App\Models\Unit;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| Dashboard Unit -- 2 JENIS TAMPILAN BERDASARKAN KATEGORI UNIT
|--------------------------------------------------------------------------
| Class ini SENGAJA tetap satu (bukan dipecah jadi dua Livewire component
| terpisah + dua route terpisah) supaya:
|   1. Route 'unit.dashboard', menu sidebar, dan link "Kembali ke Master"
|      di seluruh aplikasi tidak perlu tahu / peduli kategori unit --
|      persis seperti perilaku route 'unit.*' lain (transactions,
|      inventory, dst.) yang juga satu route untuk semua kategori unit.
|   2. EnsureUnitAccess (Master boleh monitor unit MANA PUN) otomatis
|      tetap berlaku tanpa perlu duplikasi middleware/route group.
|
| Cabang kategori HANYA terjadi di titik akhir render(): seluruh metrik
| keuangan (omzet, tren, transaksi terkini, aktivitas) tetap dihitung
| sama seperti sebelumnya untuk KEDUA kategori -- karena FinanceTransaction
| generik untuk semua unit. Yang berbeda hanya:
|   - Kategori 'ritel' (default) -> tetap render 'livewire.unit.dashboard'
|     dengan widget produk/stok seperti semula, TIDAK ADA PERUBAHAN.
|   - Kategori 'jasa' -> render 'livewire.unit.dashboard-services' dengan
|     widget tambahan seputar Pesanan Layanan (lihat
|     App\Livewire\Unit\ServiceOrder\Index & App\Models\ServiceOrder).
*/
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Dashboard Unit')]
class Dashboard extends Component
{
    // 🔴 Type-hint Model Unit agar Livewire otomatis resolve dari route-model-binding {unit}
    public Unit $unit;

    #[Url(as: 'q_trx', history: true)]
    public string $searchTransaction = '';

    // ------------------------------------------
    // FILTER PERIODE WAKTU (SAMA SEPERTI MASTER)
    // ------------------------------------------
    #[Url(as: 'period', history: true)]
    public string $periodFilter = 'this_month';

    public $startDate;
    public $endDate;

    // ------------------------------------------
    // LIFECYCLE HOOKS
    // ------------------------------------------
    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
        $this->applyPeriodFilter();
    }

    public function updatedPeriodFilter(): void
    {
        $this->applyPeriodFilter();
    }

    public function updatedStartDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
    }

    public function updatedEndDate(): void
    {
        if ($this->periodFilter !== 'custom') {
            $this->periodFilter = 'custom';
        }
    }

    private function applyPeriodFilter(): void
    {
        switch ($this->periodFilter) {
            case 'today':
                $this->startDate = Carbon::now()->toDateString();
                $this->endDate   = Carbon::now()->toDateString();
                break;
            case 'this_week':
                $this->startDate = Carbon::now()->startOfWeek()->toDateString();
                $this->endDate   = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'this_quarter':
                $this->startDate = Carbon::now()->startOfQuarter()->toDateString();
                $this->endDate   = Carbon::now()->endOfQuarter()->toDateString();
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->toDateString();
                $this->endDate   = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'custom':
                if (!$this->startDate) {
                    $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                }
                if (!$this->endDate) {
                    $this->endDate = Carbon::now()->toDateString();
                }
                break;
            case 'this_month':
            default:
                $this->startDate = Carbon::now()->startOfMonth()->toDateString();
                $this->endDate   = Carbon::now()->toDateString();
                break;
        }
    }

    /**
     * Hitung rentang periode SEBELUMNYA yang panjangnya sama persis dengan
     * rentang $start-$end yang sedang aktif, ditempel tepat sebelum $start.
     * Dipakai untuk perbandingan period-over-period (mis. "Omzet Bulan Ini
     * vs Bulan Lalu") -- berlaku untuk SEMUA pilihan $periodFilter
     * (termasuk 'custom') karena murni berbasis selisih hari, bukan
     * hardcode per jenis filter.
     *
     * @return array{0: Carbon, 1: Carbon} [$previousStart, $previousEnd]
     */
    private function previousPeriodRange(Carbon $start, Carbon $end): array
    {
        $lengthInDays = $start->diffInDays($end) + 1;

        $previousEnd   = (clone $start)->subDay()->endOfDay();
        $previousStart = (clone $previousEnd)->subDays($lengthInDays - 1)->startOfDay();

        return [$previousStart, $previousEnd];
    }

    /**
     * Persentase perubahan dari $previous ke $current, dibulatkan 1 desimal.
     * Kasus $previous == 0 ditangani manual supaya tidak division-by-zero:
     * dianggap naik 100% kalau ada nilai baru, atau 0% kalau tetap kosong.
     */
    private function percentChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function periodLabel(Carbon $start, Carbon $end): string
    {
        return match ($this->periodFilter) {
            'today'        => 'Hari Ini',
            'this_week'    => 'Minggu Ini',
            'this_month'   => 'Bulan Ini',
            'this_quarter' => 'Kuartal Ini',
            'this_year'    => 'Tahun Ini',
            'last_month'   => 'Bulan Lalu',
            'custom'       => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            default        => 'Bulan Ini',
        };
    }

    /**
     * Unit ini berkategori 'jasa'/services atau tidak. Dipakai untuk
     * memilih view dashboard yang benar di render(), dan bisa juga dipakai
     * langsung dari Blade layout kalau diperlukan.
     */
    public function isServiceCategory(): bool
    {
        return $this->unit->category === 'jasa';
    }

    /**
     * Ekspor data Dashboard Unit ke Excel (multi-sheet), scoped ke unit yang sedang login.
     */
    public function export()
    {
        Carbon::setLocale('id');

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();
        $periodLabel = $this->periodLabel($start, $end);

        $baseQuery = FinanceTransaction::query()
            ->where('unit_id', $this->unit->id)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalIncome  = (clone $baseQuery)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $trxCount     = (clone $baseQuery)->count();

        $totalProducts  = Product::where('unit_id', $this->unit->id)->count();
        $lowStockCount  = Product::where('unit_id', $this->unit->id)->whereColumn('stock', '<=', 'min_stock')->count();

        $summary = [
            'unitName'      => $this->unit->name,
            'totalIncome'   => (float) $totalIncome,
            'totalExpense'  => (float) $totalExpense,
            'netRevenue'    => (float) ($totalIncome - $totalExpense),
            'trxCount'      => (int) $trxCount,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
        ];

        $fileName = 'Laporan_Dashboard_' . \Illuminate\Support\Str::slug($this->unit->name) . '_' . now()->format('Ymd_His') . '.xlsx';

        // Catat ke Audit Log: ekspor laporan dashboard unit.
        AuditLog::record(
            'dashboard_export',
            $fileName,
            "Export laporan dashboard unit '{$this->unit->name}' periode '{$periodLabel}'",
            null,
            [
                'unit_id'       => $this->unit->id,
                'period_filter' => $this->periodFilter,
                'period_label'  => $periodLabel,
                'start_date'    => $this->startDate,
                'end_date'      => $this->endDate,
            ]
        );

        return Excel::download(
            new UnitDashboardExport($this->unit, $summary, $periodLabel, $start, $end),
            $fileName
        );
    }

    public function render()
    {
        Carbon::setLocale('id');

        $start = Carbon::parse($this->startDate)->startOfDay();
        $end   = Carbon::parse($this->endDate)->endOfDay();
        $periodLabel = $this->periodLabel($start, $end);

        $unitId = $this->unit->id;

        // -------------------------------------------------------------
        // RINGKASAN OMZET & TRANSAKSI (SCOPED KE UNIT INI SAJA)
        // SAMA UNTUK KEDUA KATEGORI: FinanceTransaction generik.
        // -------------------------------------------------------------
        $completedInRange = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end]);

        $totalIncome  = (clone $completedInRange)->where('type', 'income')->sum('amount');
        $totalExpense = (clone $completedInRange)->where('type', 'expense')->sum('amount');
        $trxCount     = (clone $completedInRange)->count();
        $avgTrxValue  = $trxCount > 0 ? ($totalIncome + $totalExpense) / $trxCount : 0;

        // -------------------------------------------------------------
        // PERBANDINGAN PERIODE SEBELUMNYA (PERIOD-OVER-PERIOD)
        // Rentang pembanding otomatis mengikuti panjang periode yang
        // sedang aktif (mis. filter "Bulan Ini" dibandingkan 30 hari
        // sebelum tanggal 1, filter "custom" 10 hari dibandingkan 10 hari
        // sebelumnya) -- lihat previousPeriodRange().
        // -------------------------------------------------------------
        [$prevStart, $prevEnd] = $this->previousPeriodRange($start, $end);

        $completedInPrevRange = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd]);

        $prevTotalIncome  = (float) (clone $completedInPrevRange)->where('type', 'income')->sum('amount');
        $prevTotalExpense = (float) (clone $completedInPrevRange)->where('type', 'expense')->sum('amount');
        $prevTrxCount     = (clone $completedInPrevRange)->count();

        $periodComparison = [
            'incomeChangePct'      => $this->percentChange((float) $totalIncome, $prevTotalIncome),
            'expenseChangePct'     => $this->percentChange((float) $totalExpense, $prevTotalExpense),
            'netRevenueChangePct'  => $this->percentChange((float) ($totalIncome - $totalExpense), $prevTotalIncome - $prevTotalExpense),
            'trxCountChangePct'    => $this->percentChange((float) $trxCount, (float) $prevTrxCount),
            'previousPeriodLabel'  => $prevStart->translatedFormat('d M Y') . ' - ' . $prevEnd->translatedFormat('d M Y'),
        ];

        // TRANSAKSI TERKINI (bisa dicari)
        $recentTransactions = FinanceTransaction::with(['category', 'user'])
            ->where('unit_id', $unitId)
            ->when($this->searchTransaction, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('description', 'like', '%' . $this->searchTransaction . '%')
                        ->orWhere('reference_no', 'like', '%' . $this->searchTransaction . '%');
                });
            })
            ->latest('transaction_date')
            ->latest('id')
            ->limit(6)
            ->get();

        // TREN OMZET HARIAN DALAM RENTANG PERIODE (untuk grafik)
        $dailyTrend = FinanceTransaction::query()
            ->where('unit_id', $unitId)
            ->where('type', 'income')
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw('transaction_date as date, SUM(amount) as total')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get();

        $revenueTrend = [
            'labels' => $dailyTrend->map(fn ($row) => Carbon::parse($row->date)->translatedFormat('d M'))->all(),
            'series' => $dailyTrend->map(fn ($row) => (float) $row->total)->all(),
        ];

        // -------------------------------------------------------------
        // TRANSAKSI BERULANG YANG AKAN JATUH TEMPO (SAMA UNTUK KEDUANYA)
        // -------------------------------------------------------------
        $upcomingRecurring = RecurringTransaction::where('unit_id', $unitId)
            ->where('status', 'active')
            ->whereNotNull('next_run_date')
            ->orderBy('next_run_date')
            ->limit(5)
            ->get();

        // -------------------------------------------------------------
        // AKTIVITAS TERKINI MILIK ADMIN UNIT INI (SAMA UNTUK KEDUANYA)
        // -------------------------------------------------------------
        $recentActivity = AuditLog::with('user')
            ->whereHas('user', fn ($q) => $q->where('unit_id', $unitId))
            ->latest()
            ->limit(6)
            ->get();

        // -------------------------------------------------------------
        // PELANGGAN (SAMA UNTUK KEDUA KATEGORI -- Manajemen Pelanggan
        // berlaku untuk unit ritel maupun jasa, lihat catatan di
        // config/menu.php & routes/web.php). Modulnya sudah ada sejak
        // awal tapi belum pernah ditarik ke dashboard.
        // -------------------------------------------------------------
        $totalActiveCustomers = Customer::where('unit_id', $unitId)
            ->where('is_active', true)
            ->count();

        $newCustomersInRange = Customer::where('unit_id', $unitId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // -------------------------------------------------------------
        // ASET UNIT USAHA (SAMA UNTUK KEDUA KATEGORI). "Perlu Perhatian"
        // = kondisi 'poor' (rusak) ATAU status 'maintenance' (sedang
        // diperbaiki) -- lihat enum yang sama dipakai di
        // App\Livewire\Master\Asset\Index / Unit\Asset\Index.
        // -------------------------------------------------------------
        $totalAssets = Asset::where('unit_id', $unitId)->count();

        $assetsNeedAttention = Asset::where('unit_id', $unitId)
            ->where(function ($q) {
                $q->where('condition', 'poor')
                  ->orWhere('status', 'maintenance');
            })
            ->count();

        // -------------------------------------------------------------
        // RINCIAN PENGELUARAN PER KATEGORI (TOP 5, PERIODE AKTIF)
        // Dipakai untuk melihat "uang keluar habis ke mana", bukan cuma
        // total pengeluaran mentah. Transaksi tanpa kategori tetap
        // dihitung di bawah label "Tanpa Kategori" supaya totalnya utuh.
        // -------------------------------------------------------------
        $expenseByCategoryRaw = FinanceTransaction::query()
            ->where('finance_transactions.unit_id', $unitId)
            ->where('finance_transactions.type', 'expense')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->leftJoin('finance_categories', 'finance_transactions.finance_category_id', '=', 'finance_categories.id')
            ->selectRaw("COALESCE(finance_categories.name, 'Tanpa Kategori') as category_name, SUM(finance_transactions.amount) as total")
            ->groupBy('category_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $expenseByCategory = $expenseByCategoryRaw->map(fn ($row) => [
            'label'      => $row->category_name,
            'total'      => (float) $row->total,
            'percentage' => $totalExpense > 0 ? round(((float) $row->total / $totalExpense) * 100, 1) : 0,
        ])->all();

        $viewData = [
            'unit'                 => $this->unit,
            'periodLabel'          => $periodLabel,
            'totalIncome'          => 'Rp ' . number_format($totalIncome, 0, ',', '.'),
            'totalExpense'         => 'Rp ' . number_format($totalExpense, 0, ',', '.'),
            'netRevenue'           => 'Rp ' . number_format($totalIncome - $totalExpense, 0, ',', '.'),
            'trxCount'             => $trxCount,
            'avgTrxValue'          => 'Rp ' . number_format($avgTrxValue, 0, ',', '.'),
            'recentTransactions'   => $recentTransactions,
            'revenueTrend'         => $revenueTrend,
            'upcomingRecurring'    => $upcomingRecurring,
            'recentActivity'       => $recentActivity,
            'periodComparison'     => $periodComparison,
            'totalActiveCustomers' => $totalActiveCustomers,
            'newCustomersInRange'  => $newCustomersInRange,
            'totalAssets'          => $totalAssets,
            'assetsNeedAttention'  => $assetsNeedAttention,
            'expenseByCategory'    => $expenseByCategory,
        ];

        // -------------------------------------------------------------
        // CABANG KATEGORI: 'jasa' (Services) vs 'ritel' (default)
        // -------------------------------------------------------------
        if ($this->isServiceCategory()) {
            // ---------------------------------------------------------
            // WIDGET KHUSUS UNIT JASA: RINGKASAN PESANAN LAYANAN
            // ---------------------------------------------------------
            $totalServiceOrders = ServiceOrder::where('unit_id', $unitId)->count();

            $pendingServiceOrders = ServiceOrder::where('unit_id', $unitId)
                ->where('status', 'pending')
                ->count();

            $inProgressServiceOrders = ServiceOrder::where('unit_id', $unitId)
                ->where('status', 'in_progress')
                ->count();

            $completedServiceOrdersInRange = ServiceOrder::where('unit_id', $unitId)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [$start, $end])
                ->count();

            $upcomingServiceOrders = ServiceOrder::where('unit_id', $unitId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('scheduled_at')
                ->orderBy('scheduled_at')
                ->limit(6)
                ->get();

            $recentServiceOrders = ServiceOrder::where('unit_id', $unitId)
                ->latest('id')
                ->limit(6)
                ->get();

            return view('livewire.unit.dashboard-services', $viewData + [
                'totalServiceOrders'            => $totalServiceOrders,
                'pendingServiceOrders'          => $pendingServiceOrders,
                'inProgressServiceOrders'       => $inProgressServiceOrders,
                'completedServiceOrdersInRange' => $completedServiceOrdersInRange,
                'upcomingServiceOrders'         => $upcomingServiceOrders,
                'recentServiceOrders'           => $recentServiceOrders,
            ]);
        }

        // ---------------------------------------------------------
        // WIDGET DEFAULT UNIT RITEL: STOK & PRODUK (TIDAK BERUBAH)
        // ---------------------------------------------------------
        $totalProducts = Product::where('unit_id', $unitId)->count();

        $lowStockProducts = Product::where('unit_id', $unitId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(6)
            ->get();

        $lowStockCount = Product::where('unit_id', $unitId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        return view('livewire.unit.dashboard', $viewData + [
            'totalProducts'    => $totalProducts,
            'lowStockProducts' => $lowStockProducts,
            'lowStockCount'    => $lowStockCount,
        ]);
    }

    public function eventInfo(string $event): array
    {
        return match ($event) {
            'login.success'                 => ['label' => 'Login Berhasil', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            'login.failed'                   => ['label' => 'Login Gagal', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            'logout'                         => ['label' => 'Logout', 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
            'password.changed'               => ['label' => 'Password Diubah', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'password.reset_by_admin'        => ['label' => 'Reset Password', 'class' => 'bg-amber-100 text-amber-700 border-amber-200'],
            'dashboard_export'               => ['label' => 'Export Laporan', 'class' => 'bg-indigo-100 text-indigo-700 border-indigo-200'],
            'access.forbidden'               => ['label' => 'Akses Ditolak', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            // Catatan casing: AuditLog::record() menyimpan $event via strtoupper(),
            // jadi key di sini WAJIB uppercase supaya match berhasil (lihat
            // App\Livewire\Unit\ServiceOrder\Index yang memanggilnya).
            'SERVICE_ORDER_CREATED'          => ['label' => 'Pesanan Layanan Ditambahkan', 'class' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
            'SERVICE_ORDER_UPDATED'          => ['label' => 'Pesanan Layanan Diperbarui', 'class' => 'bg-blue-100 text-blue-700 border-blue-200'],
            'SERVICE_ORDER_STATUS_UPDATED'   => ['label' => 'Status Layanan Diubah', 'class' => 'bg-sky-100 text-sky-700 border-sky-200'],
            'SERVICE_ORDER_DELETED'          => ['label' => 'Pesanan Layanan Dihapus', 'class' => 'bg-rose-100 text-rose-700 border-rose-200'],
            default                          => ['label' => ucwords(str_replace(['.', '_'], ' ', $event)), 'class' => 'bg-slate-100 text-slate-600 border-slate-200'],
        };
    }
}
