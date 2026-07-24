<?php

namespace Database\Seeders;

use App\Models\ProjectSiteMapping;
use App\Models\Site;
use Illuminate\Database\Seeder;

class ProjectSiteMappingSeeder extends Seeder
{
    public function run(): void
    {
        $mappings = [
            '022C' => '022C',
            '021C' => '021C',
            '017C' => '017C',
            '011C' => '011C',
            '025C' => '025C',
            '026C' => '026C',
            '023C' => '023C',
            'APS' => 'APS',
        ];

        foreach ($mappings as $projectCode => $siteCode) {
            $site = Site::where('code', $siteCode)->first();

            if (! $site) {
                continue;
            }

            ProjectSiteMapping::firstOrCreate(
                ['project_code' => $projectCode],
                ['site_id' => $site->id]
            );
        }
    }
}
