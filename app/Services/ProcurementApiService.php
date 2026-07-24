<?php

namespace App\Services;

use App\Models\ProjectSiteMapping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ProcurementApiService
{
    protected string $baseUrl;

    protected string $token;

    protected int $cacheTtl = 21600;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.arkgs.base_url'), '/');
        $this->token = (string) config('services.arkgs.token');
    }

    /**
     * @return array<string, mixed>
     */
    public function poSent(string $projectCode, int $year, int $month): array
    {
        return $this->fetch('po-sent', $projectCode, $year, $month, function () use ($projectCode, $year, $month) {
            $sample = $this->sampleData($projectCode);

            return [
                'project_code' => $projectCode,
                'year' => $year,
                'month' => $month,
                'po_amount' => $sample['po_amount'],
                'budget_amount' => $sample['budget_amount'],
                'budget_pct' => round($sample['po_amount'] / max($sample['budget_amount'], 1) * 100, 2),
                'last_synced_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function grpo(string $projectCode, int $year, int $month): array
    {
        return $this->fetch('grpo', $projectCode, $year, $month, function () use ($projectCode) {
            $sample = $this->sampleData($projectCode);
            $pct = round($sample['grpo_amount'] / max($sample['po_amount'], 1) * 100, 2);

            return [
                'project_code' => $projectCode,
                'po_amount' => $sample['po_amount'],
                'grpo_amount' => $sample['grpo_amount'],
                'completion_pct' => $pct,
                'status' => $this->grpoStatus($pct),
                'last_synced_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function npi(string $projectCode, int $year, int $month): array
    {
        return $this->fetch('npi', $projectCode, $year, $month, function () use ($projectCode) {
            $sample = $this->sampleData($projectCode);

            return [
                'project_code' => $projectCode,
                'incoming_qty' => $sample['incoming_qty'],
                'outgoing_qty' => $sample['outgoing_qty'],
                'npi_index' => $sample['npi_index'],
                'status' => $this->npiStatus($sample['npi_index']),
                'last_synced_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function budget(string $projectCode, int $year, int $month, string $type = 'regular'): array
    {
        return $this->fetch("budget:{$type}", $projectCode, $year, $month, function () use ($projectCode, $type) {
            $sample = $this->sampleData($projectCode);
            $budget = $type === 'capex' ? $sample['capex_budget'] : $sample['budget_amount'];
            $actual = $type === 'capex' ? $sample['capex_actual'] : $sample['budget_actual'];

            return [
                'project_code' => $projectCode,
                'type' => $type,
                'budget_amount' => $budget,
                'actual_amount' => $actual,
                'utilization_pct' => round($actual / max($budget, 1) * 100, 2),
                'last_synced_at' => now()->toIso8601String(),
            ];
        });
    }

    public function projectCodeForSite(int $siteId): ?string
    {
        return ProjectSiteMapping::query()
            ->where('site_id', $siteId)
            ->value('project_code');
    }

    /**
     * @return array<int, string>
     */
    public function allProjectCodes(): array
    {
        return ProjectSiteMapping::query()->pluck('project_code')->all();
    }

    public function grpoStatus(float $pct): string
    {
        if ($pct >= 80) {
            return 'good';
        }
        if ($pct >= 60) {
            return 'attention';
        }

        return 'critical';
    }

    public function npiStatus(float $index): string
    {
        if ($index <= 0.85) {
            return 'excellent';
        }
        if ($index <= 1.0) {
            return 'good';
        }
        if ($index <= 1.2) {
            return 'average';
        }
        if ($index <= 1.5) {
            return 'below';
        }

        return 'critical';
    }

    /**
     * @param  callable(): array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    protected function fetch(string $endpoint, string $projectCode, int $year, int $month, callable $fallback): array
    {
        $cacheKey = "arkgs:{$endpoint}:{$projectCode}:{$year}:{$month}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($cacheKey, $endpoint, $projectCode, $year, $month, $fallback) {
            try {
                $response = Http::withToken($this->token)
                    ->retry(3, 100)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/kpi/{$endpoint}", [
                        'project_code' => $projectCode,
                        'year' => $year,
                        'month' => $month,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data)) {
                        $data['last_synced_at'] = now()->toIso8601String();
                        $data['from_cache'] = false;

                        return $data;
                    }
                }
            } catch (\Exception) {
                // fall through
            }

            $stale = Cache::get($cacheKey);
            if (is_array($stale) && ! empty($stale)) {
                $stale['from_cache'] = true;
                $stale['stale_warning'] = true;

                return $stale;
            }

            $data = $fallback();
            $data['from_cache'] = false;
            $data['mock'] = true;

            return $data;
        });
    }

    /**
     * @return array<string, float|int>
     */
    protected function sampleData(string $projectCode): array
    {
        $hash = crc32($projectCode);

        return [
            'po_amount' => 500000000 + ($hash % 200000000),
            'grpo_amount' => 400000000 + ($hash % 150000000),
            'budget_amount' => 800000000 + ($hash % 300000000),
            'budget_actual' => 350000000 + ($hash % 200000000),
            'capex_budget' => 200000000 + ($hash % 100000000),
            'capex_actual' => 80000000 + ($hash % 50000000),
            'incoming_qty' => 1000 + ($hash % 500),
            'outgoing_qty' => 900 + ($hash % 400),
            'npi_index' => round(0.7 + ($hash % 80) / 100, 2),
        ];
    }

    /** @deprecated */
    public function fetchKpiPoSent(string $projectCode): array
    {
        return $this->poSent($projectCode, now()->year, now()->month);
    }

    /** @deprecated */
    public function fetchKpiGrpo(string $projectCode): array
    {
        return $this->grpo($projectCode, now()->year, now()->month);
    }

    /** @deprecated */
    public function fetchKpiNpi(string $projectCode): array
    {
        return $this->npi($projectCode, now()->year, now()->month);
    }

    /** @deprecated */
    public function fetchKpiBudget(string $projectCode): array
    {
        return $this->budget($projectCode, now()->year, now()->month);
    }
}
