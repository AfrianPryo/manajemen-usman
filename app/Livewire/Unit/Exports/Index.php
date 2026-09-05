<?php

namespace App\Livewire\Unit\Exports;

use App\Exports\AssetExport;
use App\Exports\AssetTemplateExport;
use App\Exports\FinanceReportExport;
use App\Exports\ProductExport;
use App\Exports\ProductTemplateExport;
use App\Exports\StockReportExport;
use App\Exports\TransactionExport;
use App\Exports\TransactionTemplateExport;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use App\Models\Asset;
use App\Models\AuditLog;
use App\Models\Category;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;
use ZipArchive;

/**
 * Versi Unit dari "Export Data" — dibuat sama persis dari segi style &
 * fungsi dengan Master\Exports\Index (tabel + bulk export + panel filter
 * per baris + section Template Import), tapi:
 *
 * - SENGAJA TIDAK extends Master\Exports\Index. Versi Master adalah pusat
 *   export lintas-unit dengan jenis data tambahan yang tidak relevan /
 *   tidak seharusnya bisa diakses oleh admin unit (Log Aktivitas Login,
 *   Audit Log Sistem, Dashboard Master Admin). Meng-extends class itu
 *   berarti mewarisi permukaan data yang lebih luas dari yang seharusnya.
 * - Tidak ada dropdown/filter "Unit Usaha" di tiap panel (seperti di versi
 *   Master) karena setiap query di sini SELALU dikunci ke unit yang sedang
 *   login lewat ScopedToUnit::currentUnitId() — baik di setiap exportX()
 *   individual maupun di buildBulkExportJobs().
 *
 * Jenis data yang didukung (5, mengikuti apa yang relevan untuk satu unit
 * usaha): Transaksi, Inventaris/Produk, Aset, Stok Barang, Laporan
 * Keuangan — masing-masing memakai class Export yang SUDAH ADA (sama
 * seperti versi Master), tinggal dikunci ke unit_id lewat filter
 * 'unitFilter' yang sudah didukung constructor-nya masing-masing.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Export Data Unit')]
class Index extends Component
{
    use ScopedToUnit;

    public ?string $openPanel = null;

    // Bulk Export: daftar key export yang dicentang ('trx', 'prod', 'asset', 'stock', 'fin')
    public array $bulkSelected = [];

    // Total jenis data yang tersedia untuk export (dipakai untuk kondisi "select all" di header tabel)
    public int $totalExportTypes = 5;

    // Filter: Transaksi
    public string $trx_search = '';
    public string $trx_typeFilter = '';
    public string $trx_statusFilter = '';
    public ?string $trx_startDate = null;
    public ?string $trx_endDate = null;

    // Filter: Inventaris / Produk
    public string $prod_search = '';
    public ?int $prod_categoryFilter = null;
    public string $prod_stockFilter = '';

    // Filter: Aset
    public string $asset_search = '';
    public string $asset_statusFilter = '';
    public string $asset_categoryFilter = '';

    // Filter: Laporan Stok
    public ?int $stock_categoryFilter = null;
    public string $stock_stockFilter = '';

    // Filter: Laporan Keuangan
    public ?string $fin_startDate = null;
    public ?string $fin_endDate = null;

    public function mount(): void
    {
        $this->trx_startDate = now()->startOfMonth()->format('Y-m-d');
        $this->trx_endDate   = now()->format('Y-m-d');
        $this->fin_startDate = now()->startOfMonth()->format('Y-m-d');
        $this->fin_endDate   = now()->format('Y-m-d');
    }

    public function togglePanel(string $key): void
    {
        $this->openPanel = $this->openPanel === $key ? null : $key;
    }

    private function unitId(): int
    {
        return $this->currentUnitId();
    }

    // =========================================================================
    // TRANSAKSI
    // =========================================================================
    public function exportTransactions()
    {
        $fileName = 'Transaksi_Unit_' . now()->format('Ymd_His') . '.xlsx';
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
            'unitFilter'   => $this->unitId(),
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
        $fileName = 'Inventaris_Unit_' . now()->format('Ymd_His') . '.xlsx';
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
            'unitFilter'     => $this->unitId(),
            'categoryFilter' => $this->prod_categoryFilter,
            'stockFilter'    => $this->prod_stockFilter,
        ];
    }

    // =========================================================================
    // ASET
    // =========================================================================
    public function exportAssets()
    {
        $fileName = 'Aset_Unit_' . now()->format('Ymd_His') . '.xlsx';
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
            'unitFilter'     => $this->unitId(),
        ];
    }

    // =========================================================================
    // LAPORAN STOK
    // =========================================================================
    public function exportStockReport()
    {
        $fileName = 'Stok_Unit_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Stok', $fileName);

        return Excel::download(new StockReportExport($this->stockFilters()), $fileName);
    }

    private function stockFilters(): array
    {
        return [
            'unitFilter'     => $this->unitId(),
            'categoryFilter' => $this->stock_categoryFilter,
            'stockFilter'    => $this->stock_stockFilter,
        ];
    }

    // =========================================================================
    // LAPORAN KEUANGAN (Multi-Sheet: Ringkasan + per Kategori)
    // =========================================================================
    public function exportFinanceReport()
    {
        $fileName = 'Laporan_Keuangan_Unit_' . now()->format('Ymd_His') . '.xlsx';
        $this->logExport('Laporan Keuangan', $fileName);

        return Excel::download(new FinanceReportExport($this->finFilters()), $fileName);
    }

    private function finFilters(): array
    {
        return [
            'unitFilter' => $this->unitId(),
            'startDate'  => $this->fin_startDate,
            'endDate'    => $this->fin_endDate,
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

        $zipFileName = 'Export_Data_Unit_' . now()->format('Ymd_His') . '.zip';
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
     * Setiap jenis data tetap memakai filter aktif dari panel masing-masing, dan tetap
     * dikunci ke unit yang sedang login lewat unitId().
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

        return $jobs;
    }

    private function logExport(string $label, string $fileName): void
    {
        AuditLog::record(
            event: 'UNIT_REPORT_EXPORTED',
            identifier: $fileName,
            description: "Admin unit mengekspor {$label} dari Pusat Export & Import Unit",
        );
    }

    public function render()
    {
        $unitId = $this->unitId();

        return view('livewire.unit.exports.index', [
            'productCategories' => Category::where('unit_id', $unitId)->orderBy('name')->get(),
            'assetCategories'   => Asset::query()
                ->where('unit_id', $unitId)
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category'),
        ]);
    }
}