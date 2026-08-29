<?php

namespace App\Exports\Dashboard\Unit;

use App\Models\Unit;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UnitDashboardExport implements WithMultipleSheets
{
    protected Unit $unit;
    protected array $summary;
    protected string $periodLabel;
    protected Carbon $start;
    protected Carbon $end;

    public function __construct(Unit $unit, array $summary, string $periodLabel, Carbon $start, Carbon $end)
    {
        $this->unit = $unit;
        $this->summary = $summary;
        $this->periodLabel = $periodLabel;
        $this->start = $start;
        $this->end = $end;
    }

    public function sheets(): array
    {
        return [
            new SummarySheetExport($this->summary, $this->periodLabel),
            new TransactionsSheetExport($this->unit->id, $this->start, $this->end),
            new StockAlertSheetExport($this->unit->id),
        ];
    }
}
