<?php

namespace App\Exports;

use App\Exports\Finance\FinanceSummarySheetExport;
use App\Exports\Finance\CategoryBreakdownSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FinanceReportExport implements WithMultipleSheets
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new FinanceSummarySheetExport($this->filters),
            new CategoryBreakdownSheetExport($this->filters),
        ];
    }
}