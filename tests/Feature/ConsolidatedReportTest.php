<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsolidatedReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_authenticated_user_can_view_consolidated_report_page(): void
    {
        $user = User::query()->where('email', 'admin@mineops.test')->firstOrFail();

        $response = $this->actingAs($user)->get(route('reports.consolidated'));

        $response->assertOk();
    }

    public function test_consolidated_api_returns_aggregated_data(): void
    {
        $user = User::query()->where('email', 'admin@mineops.test')->firstOrFail();
        $site = Site::query()->where('code', '022C')->firstOrFail();

        $response = $this->actingAs($user)->getJson('/api/dashboard/consolidated', [
            'site_ids' => [$site->id],
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-31',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'totals' => ['ob', 'coal', 'hauling', 'fuel_liters', 'sr'],
                'sites',
                'trend',
                'date_from',
                'date_to',
            ]);
    }

    public function test_consolidated_generate_validates_date_range(): void
    {
        $user = User::query()->where('email', 'admin@mineops.test')->firstOrFail();
        $site = Site::query()->where('code', '022C')->firstOrFail();

        $response = $this->actingAs($user)->post(route('reports.consolidatedGenerate'), [
            'site_ids' => [$site->id],
            'date_from' => '2026-05-31',
            'date_to' => '2026-05-01',
            'format' => 'pdf',
        ]);

        $response->assertSessionHasErrors('date_to');
    }

    public function test_consolidated_generate_returns_pdf_download(): void
    {
        $user = User::query()->where('email', 'admin@mineops.test')->firstOrFail();
        $site = Site::query()->where('code', '022C')->firstOrFail();

        $response = $this->actingAs($user)->post(route('reports.consolidatedGenerate'), [
            'site_ids' => [$site->id],
            'date_from' => '2026-05-01',
            'date_to' => '2026-05-07',
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition', ''));
    }
}
