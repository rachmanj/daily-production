<?php

namespace Database\Seeders;

use App\Enums\MaterialType;
use App\Models\EquipmentAssignment;
use App\Models\Site;
use Illuminate\Database\Seeder;

class Ccr022cEquipmentSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::where('code', '022C')->first();

        if (! $site) {
            return;
        }

        $excavators = [
            'E 090', 'E 092', 'E 071', 'E 091', 'E 076',
            'E 077', 'E 088', 'E 093', 'E 101', 'E 094',
        ];

        foreach ($excavators as $index => $unitCode) {
            EquipmentAssignment::firstOrCreate(
                [
                    'equipment_id' => 4000 + $index,
                    'site_id' => $site->id,
                ],
                [
                    'unit_code' => $unitCode,
                    'description' => "CCR {$unitCode}",
                    'plant_type_name' => 'Excavator',
                    'project_code' => '022C',
                    'material_type' => MaterialType::Overburden,
                    'equipment_role' => 'excavator',
                    'display_order' => $index + 1,
                    'is_active_for_tracking' => true,
                    'synced_at' => now(),
                ]
            );
        }

        $haulers = [];
        for ($i = 1; $i <= 28; $i++) {
            $haulers[] = 'ADT '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
        }
        $haulers[] = 'RD 038';
        $haulers[] = 'RD 076';

        foreach ($haulers as $index => $unitCode) {
            EquipmentAssignment::firstOrCreate(
                [
                    'equipment_id' => 4100 + $index,
                    'site_id' => $site->id,
                ],
                [
                    'unit_code' => $unitCode,
                    'description' => "CCR {$unitCode}",
                    'plant_type_name' => 'Hauler',
                    'project_code' => '022C',
                    'material_type' => MaterialType::Overburden,
                    'equipment_role' => 'hauler',
                    'display_order' => $index + 1,
                    'is_active_for_tracking' => true,
                    'synced_at' => now(),
                ]
            );
        }
    }
}
