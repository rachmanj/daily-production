<?php

namespace Database\Seeders;

use App\Enums\PitOwner;
use App\Models\Pit;
use App\Models\Site;
use Illuminate\Database\Seeder;

class PitSeeder extends Seeder
{
    public function run(): void
    {
        $site = Site::where('code', '022C')->first();

        if (! $site) {
            return;
        }

        foreach (['PIT1', 'PIT2'] as $code) {
            Pit::firstOrCreate(
                ['site_id' => $site->id, 'code' => $code],
                ['owner' => PitOwner::GPK, 'is_active' => true]
            );
        }
    }
}
