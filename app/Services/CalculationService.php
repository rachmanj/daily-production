<?php

namespace App\Services;

class CalculationService
{
    public function calculateMtd(int $siteId, string $metric, ?int $year = null, ?int $month = null): float
    {
        return 0.0;
    }

    public function calculateYtd(int $siteId, string $metric, ?int $year = null): float
    {
        return 0.0;
    }

    public function calculateStrippingRatio(int $siteId, ?int $year = null, ?int $month = null): ?float
    {
        return null;
    }

    public function calculateFcr(int $siteId, ?int $year = null, ?int $month = null): ?float
    {
        return null;
    }

    public function calculateAchievement(int $siteId, string $metric, ?int $year = null, ?int $month = null): ?float
    {
        return null;
    }
}
