<?php

namespace Tests\Feature;

use App\Enums\RestrictionLevel;
use App\Models\ProtectedArea;
use Database\Factories\ProtectedAreaFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProtectedAreaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ProtectedAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_bounding_box_is_calculated_from_boundary(): void
    {
        $area = ProtectedArea::factory()->create([
            'boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01),
        ]);

        $this->assertEqualsWithDelta(32.63, $area->fresh()->min_lat, 0.000001);
        $this->assertEqualsWithDelta(32.65, $area->fresh()->max_lat, 0.000001);
        $this->assertEqualsWithDelta(-16.86, $area->fresh()->min_lng, 0.000001);
        $this->assertEqualsWithDelta(-16.84, $area->fresh()->max_lng, 0.000001);
    }

    public function test_bounding_box_is_updated_when_boundary_changes(): void
    {
        $area = ProtectedArea::factory()->create();

        $area->update(['boundary' => ProtectedAreaFactory::square(33.05, -16.34, 0.02)]);

        $this->assertEqualsWithDelta(33.03, $area->fresh()->min_lat, 0.000001);
        $this->assertEqualsWithDelta(-16.32, $area->fresh()->max_lng, 0.000001);
    }

    public function test_invalid_boundary_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProtectedArea::factory()->create(['boundary' => ['type' => 'Point', 'coordinates' => [-16.9, 32.6]]]);
    }

    public function test_full_database_seeder_works_with_model_events_disabled(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertNotNull(
            ProtectedArea::where('slug', 'reserva-natural-parcial-garajau')->value('min_lat'),
        );
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ProtectedAreaSeeder::class);
        $this->seed(ProtectedAreaSeeder::class);

        $this->assertSame(1, ProtectedArea::count());
    }

    public function test_index_lists_areas_with_geojson_and_bbox(): void
    {
        ProtectedArea::factory()->create(['slug' => 'garajau', 'boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01)]);

        $this->getJson('/api/protected-areas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.boundary.type', 'Polygon')
            ->assertJsonPath('data.0.bbox', [-16.86, 32.63, -16.84, 32.65])
            ->assertJsonStructure(['data' => [[
                'slug', 'name', 'restriction_level', 'restriction_level_label', 'restrictions',
                'boundary', 'bbox', 'verified_at', 'regulation' => ['slug', 'reference'],
            ]]]);
    }

    public function test_index_filters_by_restriction_level(): void
    {
        ProtectedArea::factory()->create(['restriction_level' => RestrictionLevel::Interdita]);
        ProtectedArea::factory()->create(['restriction_level' => RestrictionLevel::Condicionada]);

        $this->getJson('/api/protected-areas?restriction_level=condicionada')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.restriction_level', 'condicionada');
    }

    public function test_index_rejects_invalid_restriction_level(): void
    {
        $this->getJson('/api/protected-areas?restriction_level=livre')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('restriction_level');
    }

    public function test_show_returns_area_by_slug(): void
    {
        ProtectedArea::factory()->create(['slug' => 'garajau', 'restriction_level' => RestrictionLevel::Interdita]);

        $this->getJson('/api/protected-areas/garajau')
            ->assertOk()
            ->assertJsonPath('data.restriction_level_label', 'Pesca interdita')
            ->assertJsonMissingPath('data.id');
    }

    public function test_soft_deleted_area_is_hidden(): void
    {
        ProtectedArea::factory()->create(['slug' => 'antiga'])->delete();

        $this->getJson('/api/protected-areas')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/protected-areas/antiga')->assertNotFound();
    }
}
