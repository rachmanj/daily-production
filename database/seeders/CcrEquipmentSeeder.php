<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Models\EquipmentAssignment;
use App\Models\Site;
use Illuminate\Database\Seeder;

class CcrEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $site021C = Site::where('code', '021C')->first();
        $site025C = Site::where('code', '025C')->first();

        if ($site021C) {
            $this->seedSiteEquipment($site021C->id, '021C', [
                ['unit_code' => 'E 084', 'material' => MaterialType::Limestone, 'order' => 1],
                ['unit_code' => 'E 098', 'material' => MaterialType::Limestone, 'order' => 2],
                ['unit_code' => 'E 095', 'material' => MaterialType::Limestone, 'order' => 3],
                ['unit_code' => 'E 089', 'material' => MaterialType::Limestone, 'order' => 4],
                ['unit_code' => 'E 082', 'material' => MaterialType::Limestone, 'order' => 5],
                ['unit_code' => 'E 078', 'material' => MaterialType::Shalestone, 'order' => 1],
                ['unit_code' => 'E 079', 'material' => MaterialType::Shalestone, 'order' => 2],
                ['unit_code' => 'E 083', 'material' => MaterialType::Shalestone, 'order' => 3],
            ], 2000);
        }

        if ($site025C) {
            $this->seedSiteEquipment($site025C->id, '025C', [
                ['unit_code' => 'E 096', 'material' => MaterialType::Limestone, 'order' => 1],
                ['unit_code' => 'E 097', 'material' => MaterialType::Limestone, 'order' => 2],
                ['unit_code' => 'E 099', 'material' => MaterialType::Limestone, 'order' => 3],
                ['unit_code' => 'E 100', 'material' => MaterialType::Limestone, 'order' => 4],
                ['unit_code' => 'WL 001', 'material' => MaterialType::Limestone, 'order' => 5],
            ], 3000);
        }
    }

    /**
     * @param  array<int, array{unit_code: string, material: MaterialType, order: int}>  $equipment
     */
    protected function seedSiteEquipment(int $siteId, string $projectCode, array $equipment, int $baseEquipmentId): void
    {
        foreach ($equipment as $index => $item) {
            EquipmentAssignment::firstOrCreate(
                [
                    'equipment_id' => $baseEquipmentId + $index,
                    'site_id' => $siteId,
                ],
                [
                    'unit_code' => $item['unit_code'],
                    'description' => "CCR {$item['unit_code']}",
                    'plant_type_name' => 'Digger',
                    'project_code' => $projectCode,
                    'material_type' => $item['material'],
                    'equipment_role' => 'loader',
                    'display_order' => $item['order'],
                    'is_active_for_tracking' => true,
                    'synced_at' => now(),
                ]
            );
        }
    }
}
