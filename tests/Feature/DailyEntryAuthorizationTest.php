<?php

namespace Tests\Feature;

use App\Enums\EntryStatus;
use App\Models\DailyEntry;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DailyEntryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->user = User::where('email', 'admin@mineops.test')->firstOrFail();
        $this->actingAs($this->user);
    }

    public function test_edit_on_approved_entry_redirects_with_error_flash_instead_of_403(): void
    {
        $site = Site::where('code', '021C')->firstOrFail();

        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => '2026-08-07',
            'site_id' => $site->id,
            'created_by' => $this->user->id,
            'status' => EntryStatus::Approved,
            'source' => 'manual',
        ]);

        $this->get(route('daily-entries.edit', $entry))
            ->assertRedirect(route('daily-entries.index'))
            ->assertSessionHas('error');
    }

    public function test_destroy_on_approved_entry_redirects_with_error_flash_instead_of_403(): void
    {
        $site = Site::where('code', '021C')->firstOrFail();

        $entry = DailyEntry::create([
            'uuid' => (string) Str::uuid(),
            'production_date' => '2026-08-08',
            'site_id' => $site->id,
            'created_by' => $this->user->id,
            'status' => EntryStatus::Approved,
            'source' => 'manual',
        ]);

        $this->delete(route('daily-entries.destroy', $entry))
            ->assertRedirect(route('daily-entries.index'))
            ->assertSessionHas('error');
    }
}
