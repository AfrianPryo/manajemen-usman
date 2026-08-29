<?php

namespace App\Livewire\Unit\Exports;

use App\Exports\FinanceReportExport;
use App\Exports\StockReportExport;
use App\Exports\TransactionExport;
use App\Models\AuditLog;
use App\Livewire\Unit\Concerns\ScopedToUnit;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Versi Unit dari "Export Data".
 *
 * SENGAJA TIDAK extends Master\Exports\Index. Versi Master adalah pusat
 * export lintas-unit dengan 8 jenis data (termasuk Auth Log & Audit Log
 * sistem yang tidak relevan/tidak seharusnya bisa diakses oleh admin unit).
 * Meng-extends class itu berarti mewarisi jauh lebih banyak permukaan data
 * daripada yang seharusnya bisa diakses oleh unit-admin.
 *
 * Sebagai gantinya, class ini berdiri sendiri dan hanya membungkus 3 jenis
 * export yang relevan untuk satu unit usaha, dengan memanfaatkan class
 * Export yang SUDAH ADA (TransactionExport, StockReportExport,
 * FinanceReportExport) — ketiganya sudah menerima key filter 'unitFilter'
 * di constructor-nya (lihat masing-masing file di app/Exports/), jadi di
 * sini tinggal dikunci ke unit_id user login, tidak perlu bikin class
 * Export baru.
 */
#[Layout('components.layouts.unit', [
    'category' => 'Unit Usaha',
    'role'     => 'unit',
])]
#[Title('Export Data Unit')]
class Index extends Component
{
    use ScopedToUnit;

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    private function unitId(): int
    {
        return $this->currentUnitId();
    }

    public function exportTransactions()
    {
        $filters = [
            'unitFilter' => $this->unitId(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $fileName = 'Transaksi_Unit_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            'UNIT_TRANSACTIONS_EXPORTED',
            $fileName,
            "Admin unit mengekspor data transaksi periode {$this->startDate} s/d {$this->endDate}."
        );

        return Excel::download(new TransactionExport($filters), $fileName);
    }

    public function exportStock()
    {
        $filters = ['unitFilter' => $this->unitId()];

        $fileName = 'Stok_Unit_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record('UNIT_STOCK_EXPORTED', $fileName, 'Admin unit mengekspor data stok/inventaris.');

        return Excel::download(new StockReportExport($filters), $fileName);
    }

    public function exportFinanceReport()
    {
        $filters = [
            'unitFilter' => $this->unitId(),
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $fileName = 'Laporan_Keuangan_Unit_' . now()->format('Ymd_His') . '.xlsx';

        AuditLog::record(
            'UNIT_FINANCE_REPORT_EXPORTED',
            $fileName,
            "Admin unit mengekspor laporan keuangan periode {$this->startDate} s/d {$this->endDate}."
        );

        return Excel::download(new FinanceReportExport($filters), $fileName);
    }

    public function render()
    {
        return view('livewire.unit.exports.index');
    }
}
