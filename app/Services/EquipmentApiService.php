<?php

namespace App\Services;

class EquipmentApiService
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchEquipment(string $projectCode): array
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchHmKmReadings(int $equipmentId, string $date): array
    {
        return [];
    }
}
