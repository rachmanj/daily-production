<?php

namespace App\Exports;

use App\Enums\MaterialType;
use App\Models\Site;
use App\Services\CalculationService;
use App\Services\HourlyProductionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class HourlyPdfExport
{
    public function __construct(
        protected int $siteId,
        protected Carbon $date,
        protected MaterialType $material,
        protected CalculationService $calculationService,
        protected HourlyProductionService $hourlyProductionService,
    ) {}

    public function download(): Response
    {
        $site = Site::findOrFail($this->siteId);
        $equipment = $this->hourlyProductionService->getEquipmentGrid($this->siteId, $this->material);
        $hourlyTarget = $this->calculationService->hourlyTarget($this->siteId, $this->date, $this->material);

        $excelExport = new HourlyProductionExport(
            $this->siteId,
            $this->date,
            $this->material,
            $this->hourlyProductionService,
        );

        $dtdActual = $this->calculationService->materialDtd($this->siteId, $this->date, $this->material);
        $mtdActual = $this->calculationService->materialMtd($this->siteId, $this->date, $this->material);
        $dtdPlan = $this->calculationService->materialPlanDaily($this->siteId, $this->date, $this->material);
        $mtdPlan = $this->calculationService->materialPlanMonthly($this->siteId, $this->date, $this->material);

        $pdf = Pdf::loadView('reports.hourly', [
            'site' => $site,
            'date' => $this->date,
            'material' => $this->material,
            'equipment' => $equipment,
            'rows' => $excelExport->array(),
            'headings' => $excelExport->headings(),
            'hourlyTarget' => $hourlyTarget,
            'dtd' => ['actual' => $dtdActual, 'plan' => $dtdPlan],
            'mtd' => ['actual' => $mtdActual, 'plan' => $mtdPlan],
        ]);

        $filename = "ccr-hourly-{$site->code}-{$this->date->format('Y-m-d')}-{$this->material->value}.pdf";

        return $pdf->download($filename);
    }
}
