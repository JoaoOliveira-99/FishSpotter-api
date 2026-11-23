<?php

namespace Tests\Feature;

use App\Models\FishingType;
use Database\Seeders\FishingTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingTypeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(FishingTypeSeeder::class);
    }

    public function test_seeder_is_idempotent(): void
    {
        $count = FishingType::count();

        $this->seed(FishingTypeSeeder::class);

        $this->assertSame($count, FishingType::count());
    }

    public function test_index_lists_fishing_types(): void
    {
        $this->getJson('/api/fishing-types')
            ->assertOk()
            ->assertJsonCount(FishingType::count(), 'data')
            ->assertJsonStructure(['data' => [[
                'slug', 'name', 'description', 'license_modality', 'license_modality_label',
            ]]]);
    }

    public function test_show_returns_license_modality_when_set(): void
    {
        $this->getJson('/api/fishing-types/submarina')
            ->assertOk()
            ->assertJsonPath('data.license_modality', 'submarina')
            ->assertJsonPath('data.license_modality_label', 'Pesca submarina');
    }

    public function test_show_returns_null_license_modality_when_not_set(): void
    {
        $this->getJson('/api/fishing-types/spinning')
            ->assertOk()
            ->assertJsonPath('data.license_modality', null)
            ->assertJsonPath('data.license_modality_label', null);
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->getJson('/api/fishing-types/nao-existe')->assertNotFound();
    }
}
