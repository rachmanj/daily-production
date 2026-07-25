<?php

namespace App\Exports;

use App\Exports\Consolidated\DeploymentSheetExport;
use App\Exports\Consolidated\FuelSheetExport;
use App\Exports\Consolidated\ProductionSheetExport;
use App\Exports\Consolidated\SiteInfoSheetExport;
use App\Exports\Consolidated\SummarySheetExport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ConsolidatedReportExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        protected array $data,
    ) {}

    /**
     * @return array<int, object>
     */
    public function sheets(): array
    {
        return [
            new SummarySheetExport($this->data),
            new ProductionSheetExport($this->data),
            new FuelSheetExport($this->data),
            new DeploymentSheetExport($this->data),
            new SiteInfoSheetExport($this->data),
        ];
    }
}
