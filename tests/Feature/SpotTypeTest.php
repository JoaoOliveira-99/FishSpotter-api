<?php

namespace Tests\Feature;

use App\Models\SpotType;
use Database\Seeders\SpotTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpotTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SpotTypeSeeder::class);
    }

    public function test_seeder_is_idempotent(): void
    {
        $count = SpotType::count();

        $this->seed(SpotTypeSeeder::class);

        $this->assertSame($count, SpotType::count());
    }

    public function test_index_lists_spot_types_ordered_by_name(): void
    {
        $this->getJson('/api/spot-types')
            ->assertOk()
            ->assertJsonCount(SpotType::count(), 'data')
            ->assertJsonPath('data.0.slug', 'cais')
            ->assertJsonStructure(['data' => [['slug', 'name']]]);
    }

    public function test_show_returns_spot_type_by_slug(): void
    {
        $this->getJson('/api/spot-types/costa-rochosa')
            ->assertOk()
            ->assertJsonPath('data.name', 'Costa rochosa')
            ->assertJsonMissingPath('data.id');
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/spot-types/nao-existe')->assertNotFound();
    }
}
