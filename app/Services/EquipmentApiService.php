<?php

namespace App\Services;

use App\Models\EquipmentAssignment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class EquipmentApiService
{
    protected string $baseUrl;

    protected string $token;

    protected int $cacheTtl = 3600;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.arkfleet.base_url'), '/');
        $this->token = (string) config('services.arkfleet.token');
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function search(array $filters = []): array
    {
        $cacheKey = 'equipment:search:'.md5(json_encode($filters) ?: '');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($filters, $cacheKey) {
            try {
                $response = Http::withToken($this->token)
                    ->retry(3, 100)
                    ->timeout(10)
                    ->get($this->baseUrl.'/equipment', $filters);

                if ($response->successful()) {
                    $data = $response->json('data', []);

                    return is_array($data) ? $data : [];
                }
            } catch (\Exception) {
                // Graceful degradation — fall through to fallback
            }

            $stale = Cache::get($cacheKey);
            if (is_array($stale) && $stale !== []) {
                return $stale;
            }

            return $this->fallbackSearch($filters);
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return Cache::remember("equipment:detail:{$id}", $this->cacheTtl, function () use ($id) {
            try {
                $response = Http::withToken($this->token)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/equipment/{$id}");

                if ($response->successful()) {
                    $data = $response->json('data');

                    return is_array($data) ? $data : null;
                }
            } catch (\Exception) {
                // Graceful degradation
            }

            $assignment = EquipmentAssignment::query()->where('equipment_id', $id)->first();

            if (! $assignment) {
                return null;
            }

            return [
                'id' => $assignment->equipment_id,
                'unit_code' => $assignment->unit_code,
                'description' => $assignment->description,
                'plant_type_name' => $assignment->plant_type_name,
                'project_code' => $assignment->project_code,
            ];
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function hmKmReadings(int $equipmentId): array
    {
        return Cache::remember("equipment:hmkm:{$equipmentId}", $this->cacheTtl, function () use ($equipmentId) {
            try {
                $response = Http::withToken($this->token)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/equipment/{$equipmentId}/hm-km-readings");

                if ($response->successful()) {
                    $data = $response->json('data', []);

                    return is_array($data) ? $data : [];
                }
            } catch (\Exception) {
                // Graceful degradation
            }

            return [];
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    protected function fallbackSearch(array $filters): array
    {
        $query = EquipmentAssignment::query()
            ->select([
                'equipment_id as id',
                'unit_code',
                'description',
                'plant_type_name',
                'project_code',
                'site_id',
                'pit_id',
            ]);

        if (! empty($filters['project_code'])) {
            $query->where('project_code', $filters['project_code']);
        }

        if (! empty($filters['plant_type'])) {
            $query->where('plant_type_name', $filters['plant_type']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('unit_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active_for_tracking', (bool) $filters['is_active']);
        }

        return $query->get()->map(function (EquipmentAssignment $assignment) {
            return [
                'id' => $assignment->equipment_id,
                'unit_code' => $assignment->unit_code,
                'description' => $assignment->description,
                'plant_type_name' => $assignment->plant_type_name,
                'project_code' => $assignment->project_code,
                'site_id' => $assignment->site_id,
                'pit_id' => $assignment->pit_id,
            ];
        })->all();
    }
}
