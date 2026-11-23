<?php

namespace Tests\Feature;

use App\Enums\BaitKind;
use App\Models\Bait;
use Database\Seeders\BaitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BaitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(BaitSeeder::class);
    }

    public function test_seeder_is_idempotent(): void
    {
        $count = Bait::count();

        $this->seed(BaitSeeder::class);

        $this->assertSame($count, Bait::count());
    }

    public function test_index_lists_all_baits(): void
    {
        $this->getJson('/api/baits')
            ->assertOk()
            ->assertJsonCount(Bait::count(), 'data')
            ->assertJsonStructure(['data' => [['slug', 'name', 'kind', 'kind_label']]]);
    }

    public function test_index_filters_by_kind(): void
    {
        $naturalCount = Bait::where('kind', BaitKind::Natural)->count();

        $response = $this->getJson('/api/baits?kind=natural')
            ->assertOk()
            ->assertJsonCount($naturalCount, 'data');

        $this->assertSame(
            ['natural'],
            collect($response->json('data'))->pluck('kind')->unique()->values()->all(),
        );
    }

    public function test_index_rejects_invalid_kind(): void
    {
        $this->getJson('/api/baits?kind=vivo')
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors('kind');
    }

    public function test_show_returns_bait_by_slug(): void
    {
        $this->getJson('/api/baits/jig-metalico')
            ->assertOk()
            ->assertJsonPath('data.name', 'Jig metálico')
            ->assertJsonPath('data.kind', 'artificial')
            ->assertJsonPath('data.kind_label', 'Amostra artificial')
            ->assertJsonMissingPath('data.id');
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/baits/nao-existe')
            ->assertNotFound()
            ->assertJsonPath('code', 'not_found');
    }
}
