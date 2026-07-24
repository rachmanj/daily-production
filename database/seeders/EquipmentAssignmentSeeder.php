<?php

namespace Database\Seeders;

use App\Models\EquipmentAssignment;
use App\Models\Pit;
use App\Models\Site;
use Illuminate\Database\Seeder;

class EquipmentAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::where('code', '022C')->first();

        if (! $site) {
            return;
        }

        $pit1 = Pit::where('site_id', $site->id)->where('code', 'PIT1')->first();
        $pit2 = Pit::where('site_id', $site->id)->where('code', 'PIT2')->first();

        if (! $pit1 || ! $pit2) {
            return;
        }

        $equipment = $this->equipmentList();
        $equipmentId = 1000;

        foreach ($equipment as $index => $item) {
            $pit = $index % 2 === 0 ? $pit1 : $pit2;

            EquipmentAssignment::firstOrCreate(
                [
                    'equipment_id' => $equipmentId + $index,
                    'site_id' => $site->id,
                ],
                [
                    'unit_code' => $item['unit_code'],
                    'description' => $item['description'],
                    'plant_type_name' => $item['plant_type_name'],
                    'project_code' => '022C',
                    'pit_id' => $pit->id,
                    'is_active_for_tracking' => true,
                    'synced_at' => now(),
                ]
            );
        }
    }

    /**
     * @return array<int, array{unit_code: string, description: string, plant_type_name: string}>
     */
    protected function equipmentList(): array
    {
        $list = [];

        $excavators = ['E 062', 'E 071', 'E 076', 'E 077', 'E 080', 'E 081', 'E 085', 'E 087', 'E 088', 'E 090', 'E 091', 'E 092', 'E 093', 'E 094', 'E 101'];
        foreach ($excavators as $code) {
            $list[] = [
                'unit_code' => $code,
                'description' => "Excavator {$code}",
                'plant_type_name' => 'Digger',
            ];
        }

        $dozers = ['DZ 031', 'DZ 037', 'DZ 040', 'DZ 042', 'DZ 043', 'DZ 044'];
        foreach ($dozers as $code) {
            $list[] = [
                'unit_code' => $code,
                'description' => "Dozer {$code}",
                'plant_type_name' => 'Support',
            ];
        }

        for ($i = 1; $i <= 28; $i++) {
            $list[] = [
                'unit_code' => sprintf('ADT %03d', $i),
                'description' => 'Articulated Dump Truck ADT '.sprintf('%03d', $i),
                'plant_type_name' => 'Hauler',
            ];
        }

        foreach (['T 071', 'T 074'] as $code) {
            $list[] = [
                'unit_code' => $code,
                'description' => "Hauler {$code}",
                'plant_type_name' => 'Hauler',
            ];
        }

        for ($i = 112; $i <= 122; $i++) {
            $list[] = [
                'unit_code' => "T {$i}",
                'description' => "Dump Truck T {$i}",
                'plant_type_name' => 'Hauler',
            ];
        }

        for ($i = 1; $i <= 5; $i++) {
            $list[] = [
                'unit_code' => sprintf('FT %03d', $i),
                'description' => 'Fuel Truck FT '.sprintf('%03d', $i),
                'plant_type_name' => 'Support',
            ];
        }

        for ($i = 1; $i <= 3; $i++) {
            $list[] = [
                'unit_code' => sprintf('ST %03d', $i),
                'description' => 'Service Truck ST '.sprintf('%03d', $i),
                'plant_type_name' => 'Support',
            ];
        }

        for ($i = 1; $i <= 7; $i++) {
            $list[] = [
                'unit_code' => sprintf('WT %03d', $i),
                'description' => 'Water Truck WT '.sprintf('%03d', $i),
                'plant_type_name' => 'Support',
            ];
        }

        $graders = ['GR 001', 'GR 002', 'GR 003'];
        foreach ($graders as $code) {
            $list[] = [
                'unit_code' => $code,
                'description' => "Grader {$code}",
                'plant_type_name' => 'Support',
            ];
        }

        $wheelLoaders = ['WL 011', 'WL 012', 'WL 015'];
        foreach ($wheelLoaders as $code) {
            $list[] = [
                'unit_code' => $code,
                'description' => "Wheel Loader {$code}",
                'plant_type_name' => 'Digger',
            ];
        }

        return $list;
    }
}
