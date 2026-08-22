<?php

namespace App\Exports;

use App\Exports\Dashboard\SummarySheetExport;
use App\Exports\Dashboard\AdminSheetExport;
use App\Exports\Dashboard\UnitSheetExport;
use App\Exports\Dashboard\RevenueContributionSheetExport;
use App\Exports\Dashboard\RecentTransactionsSheetExport;
use App\Exports\Dashboard\ActivityLogSheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardExport implements WithMultipleSheets
{
    protected array $summary;
    protected array $filters;
    protected array $revenueContribution;
    protected string $periodLabel;

    public function __construct(array $summary, array $filters, array $revenueContribution, string $periodLabel)
    {
        $this->summary = $summary;
        $this->filters = $filters;
        $this->revenueContribution = $revenueContribution;
        $this->periodLabel = $periodLabel;
    }

    public function sheets(): array
    {
        return [
            new SummarySheetExport($this->summary, $this->periodLabel),
            new RevenueContributionSheetExport($this->revenueContribution, $this->periodLabel),
            new UnitSheetExport($this->filters['searchUnit'] ?? null),
            new AdminSheetExport($this->filters['searchAdmin'] ?? null),
            new RecentTransactionsSheetExport(),
            new ActivityLogSheetExport(),
        ];
    }
}