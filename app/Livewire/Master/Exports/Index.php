<?php

namespace App\Livewire\Master\Exports;

use App\Exports\TransactionExport;
use App\Exports\TransactionTemplateExport;
use App\Exports\ProductExport;
use App\Exports\ProductTemplateExport;
use App\Exports\AssetExport;
use App\Exports\AssetTemplateExport;
use App\Exports\DashboardExport;
use App\Exports\StockReportExport;
use App\Exports\FinanceReportExport;
use App\Exports\AuthLogExport;
use App\Exports\AuditLogExport;
use App\Models\AuditLog;
use App\Models\AuthLog;
use App\Models\Asset;
use App\Models\Category;
use App\Models\FinanceTransaction;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use ZipArchive;

#[Layout('components.layouts.app')]
#[Title('Export & Import Data')]
class Index extends Component
{
    public ?string $openPanel = null;

    // Bulk Export: daftar key export yang dicentang ('trx', 'prod', 'asset', 'stock', 'fin', 'authlog', 'auditlog', 'dash')
    public array $bulkSelected = [];

    // Total jenis data yang tersedia untuk export (dipakai untuk kondisi "select all" di header tabel)
    public int $totalExportTypes = 8;

    // Filter: Transaksi
    public string $trx_search = '';
    public ?int $trx_unitFilter = null;
    public string $trx_typeFilter = '';
    public string $trx_statusFilter = '';
    public ?string $trx_startDate = null;
    public ?string $trx_endDate = null;

    // Filter: Inventaris / Produk
    public string $prod_search = '';
    public ?int $prod_unitFilter = null;
    public ?int $prod_categoryFilter = null;
    public string $prod_stockFilter = '';

    // Filter: Aset
    public string $asset_search = '';
    public string $asset_statusFilter = '';
    public string $asset_categoryFilter = '';

    // Filter: Dashboard Master Admin
    public string $dash_periodFilter = 'this_month';
    public ?string $dash_startDate = null;
    public ?string $dash_endDate = null;

    // Filter: Laporan Stok
    public ?int $stock_unitFilter = null;
    public ?int $stock_categoryFilter = null;
    public string $stock_stockFilter = '';

    // Filter: Laporan Keuangan
    public ?int $fin_unitFilter = null;
    public ?string $fin_startDate = null;
    public ?string $fin_endDate = null;

    // Filter: Log Aktivitas Login
    public string $authlog_search = '';
    public string $authlog_eventFilter = '';

    // Filter: Audit Log Sistem
    public string $auditlog_search = '';
    public string $auditlog_eventFilter = '';
    public ?string $auditlog_startDate = null;
    public ?string $auditlog_endDate = null;

    public function togglePanel(string $key): void
    {
        $this->openPanel = $this->openPanel === $key ? null : $key;
    }

    // =========================================================================
    // TRANSAKSI
    // =========================================================================
    public function exportTransactions()
    {
        $fileName = 'Laporan_Transaksi_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Transaksi', $fileName);

        return Excel::download(new TransactionExport($this->trxFilters()), $fileName);
    }

    public function downloadTransactionTemplate()
    {
        return Excel::download(new TransactionTemplateExport, 'Template_Import_Transaksi.xlsx');
    }

    private function trxFilters(): array
    {
        return [
            'search'       => $this->trx_search,
            'unitFilter'   => $this->trx_unitFilter,
            'typeFilter'   => $this->trx_typeFilter,
            'statusFilter' => $this->trx_statusFilter,
            'startDate'    => $this->trx_startDate,
            'endDate'      => $this->trx_endDate,
        ];
    }

    // =========================================================================
    // INVENTARIS / PRODUK
    // =========================================================================
    public function exportProducts()
    {
        $fileName = 'Laporan_Inventaris_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Inventaris', $fileName);

        return Excel::download(new ProductExport($this->prodFilters()), $fileName);
    }

    public function downloadProductTemplate()
    {
        return Excel::download(new ProductTemplateExport, 'Template_Import_Produk.xlsx');
    }

    private function prodFilters(): array
    {
        return [
            'search'         => $this->prod_search,
            'unitFilter'     => $this->prod_unitFilter,
            'categoryFilter' => $this->prod_categoryFilter,
            'stockFilter'    => $this->prod_stockFilter,
        ];
    }

    // =========================================================================
    // ASET
    // =========================================================================
    public function exportAssets()
    {
        $fileName = 'Laporan_Aset_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Aset', $fileName);

        return Excel::download(new AssetExport($this->assetFilters()), $fileName);
    }

    public function downloadAssetTemplate()
    {
        return Excel::download(new AssetTemplateExport, 'Template_Import_Aset.xlsx');
    }

    private function assetFilters(): array
    {
        return [
            'search'         => $this->asset_search,
            'statusFilter'   => $this->asset_statusFilter,
            'categoryFilter' => $this->asset_categoryFilter,
        ];
    }

    // =========================================================================
    // DASHBOARD MASTER ADMIN (Multi-Sheet)
    // =========================================================================
    public function exportDashboardReport()
    {
        $fileName = 'Laporan_Master_Admin_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Dashboard Master Admin', $fileName);

        return Excel::download($this->buildDashboardExport(), $fileName);
    }

    /**
     * Menyiapkan objek DashboardExport (dipakai baik oleh export tunggal maupun bulk export).
     */
    private function buildDashboardExport(): DashboardExport
    {
        $this->applyDashPeriodFilter();

        $start = Carbon::parse($this->dash_startDate)->startOfDay();
        $end   = Carbon::parse($this->dash_endDate)->endOfDay();

        $periodLabel = match ($this->dash_periodFilter) {
            'today'        => 'Hari Ini',
            'this_week'    => 'Minggu Ini',
            'this_month'   => 'Bulan Ini',
            'this_quarter' => 'Kuartal Ini',
            'this_year'    => 'Tahun Ini',
            'last_month'   => 'Bulan Lalu',
            'custom'       => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            default        => 'Bulan Ini',
        };

        $unitContributions = FinanceTransaction::query()
            ->join('units', 'finance_transactions.unit_id', '=', 'units.id')
            ->where('finance_transactions.type', 'income')
            ->where('finance_transactions.status', 'completed')
            ->whereBetween('finance_transactions.transaction_date', [$start, $end])
            ->selectRaw('
                units.name,
                SUM(finance_transactions.amount) as total_income,
                COUNT(finance_transactions.id) as trx_count,
                AVG(finance_transactions.amount) as avg_amount,
                MIN(finance_transactions.transaction_date) as first_trx_date,
                MAX(finance_transactions.transaction_date) as last_trx_date
            ')
            ->groupBy('units.id', 'units.name')
            ->orderByDesc('total_income')
            ->get();

        $grandTotalContribution = $unitContributions->sum('total_income');

        $revenueContribution = [
            'labels' => [], 'series' => [], 'percentages' => [],
            'counts' => [], 'averages' => [], 'firstDates' => [], 'lastDates' => [],
        ];

        foreach ($unitContributions as $contrib) {
            $val = (float) $contrib->total_income;
            $revenueContribution['labels'][]      = $contrib->name;
            $revenueContribution['series'][]      = $val;
            $revenueContribution['percentages'][] = $grandTotalContribution > 0
                ? round(($val / $grandTotalContribution) * 100, 1)
                : 0;
            $revenueContribution['counts'][]     = (int) $contrib->trx_count;
            $revenueContribution['averages'][]   = (float) $contrib->avg_amount;
            $revenueContribution['firstDates'][] = $contrib->first_trx_date;
            $revenueContribution['lastDates'][]  = $contrib->last_trx_date;
        }

        $allUnitsCount    = Unit::count();
        $activeUnitsCount = Unit::where('is_active', true)->count();

        $summary = [
            'totalRevenue'        => $grandTotalContribution,
            'totalUnits'          => $allUnitsCount,
            'activeUnits'         => $activeUnitsCount,
            'inactiveUnits'       => $allUnitsCount - $activeUnitsCount,
            'totalAdmins'         => User::all()->filter(fn ($u) => method_exists($u, 'isUnitAdmin') ? $u->isUnitAdmin() : true)->count(),
            'totalTransactions'   => (int) $unitContributions->sum('trx_count'),
            'avgTransactionValue' => $unitContributions->sum('trx_count') > 0
                ? $grandTotalContribution / $unitContributions->sum('trx_count')
                : 0,
        ];

        return new DashboardExport($summary, [], $revenueContribution, $periodLabel);
    }

    private function applyDashPeriodFilter(): void
    {
        switch ($this->dash_periodFilter) {
            case 'today':
                $this->dash_startDate = Carbon::now()->toDateString();
                $this->dash_endDate   = Carbon::now()->toDateString();
                break;
            case 'this_week':
                $this->dash_startDate = Carbon::now()->startOfWeek()->toDateString();
                $this->dash_endDate   = Carbon::now()->endOfWeek()->toDateString();
                break;
            case 'this_quarter':
                $this->dash_startDate = Carbon::now()->startOfQuarter()->toDateString();
                $this->dash_endDate   = Carbon::now()->endOfQuarter()->toDateString();
                break;
            case 'this_year':
                $this->dash_startDate = Carbon::now()->startOfYear()->toDateString();
                $this->dash_endDate   = Carbon::now()->endOfYear()->toDateString();
                break;
            case 'last_month':
                $this->dash_startDate = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                $this->dash_endDate   = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'custom':
                if (!$this->dash_startDate) $this->dash_startDate = Carbon::now()->startOfMonth()->toDateString();
                if (!$this->dash_endDate) $this->dash_endDate = Carbon::now()->toDateString();
                break;
            case 'this_month':
            default:
                $this->dash_startDate = Carbon::now()->startOfMonth()->toDateString();
                $this->dash_endDate   = Carbon::now()->toDateString();
                break;
        }
    }

    // =========================================================================
    // LAPORAN STOK
    // =========================================================================
    public function exportStockReport()
    {
        $fileName = 'Laporan_Stok_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Stok', $fileName);

        return Excel::download(new StockReportExport($this->stockFilters()), $fileName);
    }

    private function stockFilters(): array
    {
        return [
            'unitFilter'     => $this->stock_unitFilter,
            'categoryFilter' => $this->stock_categoryFilter,
            'stockFilter'    => $this->stock_stockFilter,
        ];
    }

    // =========================================================================
    // LAPORAN KEUANGAN (Multi-Sheet: Ringkasan + per Kategori)
    // =========================================================================
    public function exportFinanceReport()
    {
        $fileName = 'Laporan_Keuangan_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Keuangan', $fileName);

        return Excel::download(new FinanceReportExport($this->finFilters()), $fileName);
    }

    private function finFilters(): array
    {
        return [
            'unitFilter' => $this->fin_unitFilter,
            'startDate'  => $this->fin_startDate,
            'endDate'    => $this->fin_endDate,
        ];
    }

    // =========================================================================
    // LOG AKTIVITAS LOGIN
    // =========================================================================
    public function exportAuthLogs()
    {
        $fileName = 'Log_Aktivitas_Login_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Log Aktivitas Login', $fileName);

        return Excel::download(new AuthLogExport($this->authlogFilters()), $fileName);
    }

    private function authlogFilters(): array
    {
        return [
            'search'      => $this->authlog_search,
            'eventFilter' => $this->authlog_eventFilter,
        ];
    }

    // =========================================================================
    // AUDIT LOG SISTEM
    // =========================================================================
    public function exportAuditLogs()
    {
        $fileName = 'Audit_Log_Sistem_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Audit Log Sistem', $fileName);

        return Excel::download(new AuditLogExport($this->auditlogFilters()), $fileName);
    }

    private function auditlogFilters(): array
    {
        return [
            'search'      => $this->auditlog_search,
            'eventFilter' => $this->auditlog_eventFilter,
            'startDate'   => $this->auditlog_startDate,
            'endDate'     => $this->auditlog_endDate,
        ];
    }

    // =========================================================================
    // BULK EXPORT — menggabungkan beberapa jenis export terpilih ke satu .zip
    // =========================================================================
    public function bulkExport()
    {
        if (empty($this->bulkSelected)) {
            return;
        }

        $jobs = $this->buildBulkExportJobs();

        if (empty($jobs)) {
            return;
        }

        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipFileName = 'Export_Data_' . now()->format('Ymd_His') . '.zip';
        $zipPath = $tmpDir . '/' . $zipFileName;

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($jobs as $fileName => $exportInstance) {
            // Excel::raw() merender file di memori (mendukung sheet tunggal maupun multi-sheet)
            $binary = Excel::raw($exportInstance, ExcelFormat::XLSX);
            $zip->addFromString($fileName, $binary);
        }

        $zip->close();

        $this->logExport('Export Massal (' . count($jobs) . ' jenis data)', $zipFileName);

        $selectedCount = count($this->bulkSelected);
        $this->bulkSelected = [];
        $this->dispatch('bulk-export-done', count: $selectedCount);

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Memetakan setiap key terpilih ('trx', 'prod', dst) ke instance Export + nama file di dalam zip.
     * Setiap jenis data tetap memakai filter aktif dari panel masing-masing.
     *
     * @return array<string, object> [namaFileDalamZip => instance export]
     */
    private function buildBulkExportJobs(): array
    {
        $jobs = [];

        if (in_array('trx', $this->bulkSelected, true)) {
            $jobs['Data_Transaksi.xlsx'] = new TransactionExport($this->trxFilters());
        }

        if (in_array('prod', $this->bulkSelected, true)) {
            $jobs['Data_Inventaris.xlsx'] = new ProductExport($this->prodFilters());
        }

        if (in_array('asset', $this->bulkSelected, true)) {
            $jobs['Data_Aset.xlsx'] = new AssetExport($this->assetFilters());
        }

        if (in_array('stock', $this->bulkSelected, true)) {
            $jobs['Data_Stok.xlsx'] = new StockReportExport($this->stockFilters());
        }

        if (in_array('fin', $this->bulkSelected, true)) {
            $jobs['Data_Keuangan.xlsx'] = new FinanceReportExport($this->finFilters());
        }

        if (in_array('authlog', $this->bulkSelected, true)) {
            $jobs['Log_Aktivitas_Login.xlsx'] = new AuthLogExport($this->authlogFilters());
        }

        if (in_array('auditlog', $this->bulkSelected, true)) {
            $jobs['Audit_Log_Sistem.xlsx'] = new AuditLogExport($this->auditlogFilters());
        }

        if (in_array('dash', $this->bulkSelected, true)) {
            $jobs['Data_Dashboard_Master_Admin.xlsx'] = $this->buildDashboardExport();
        }

        return $jobs;
    }

    private function logExport(string $label, string $fileName): void
    {
        AuditLog::record(
            event: 'REPORT_EXPORTED',
            identifier: $fileName,
            description: "Admin mengekspor {$label} dari Pusat Export & Import",
        );
    }

    public function render()
    {
        return view('livewire.master.exports.index', [
            'units'             => Unit::orderBy('name')->get(),
            'productCategories' => Category::orderBy('name')->get(),
            'assetCategories'   => Asset::query()->whereNotNull('category')->distinct()->pluck('category'),
            'authLogEvents'     => AuthLog::query()->whereNotNull('event')->distinct()->pluck('event'),
            'auditLogEvents'    => AuditLog::query()
                ->whereNotIn('event', ['USER_LOGIN', 'USER_LOGOUT', 'LOGIN_FAILED'])
                ->whereNotNull('event')
                ->distinct()
                ->pluck('event'),
        ]);
    }
}