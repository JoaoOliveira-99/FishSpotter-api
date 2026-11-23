<?php

namespace Tests\Feature;

use App\Enums\Coast;
use App\Enums\SpotStatus;
use App\Models\FishingSpot;
use App\Models\FishingType;
use App\Models\ForecastPoint;
use App\Models\ProtectedArea;
use Database\Factories\ProtectedAreaFactory;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FishingSpotSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class FishingSpotTest extends TestCase
{
    use RefreshDatabase;

    // ─── Geografia calculada ao gravar ──────────────────────────────────

    public function test_spot_inside_protected_area_is_linked_automatically(): void
    {
        $area = ProtectedArea::factory()->create(['boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01)]);

        $spot = FishingSpot::factory()->at(32.641, -16.851)->create();

        $this->assertSame($area->id, $spot->protected_area_id);
    }

    public function test_spot_outside_protected_area_is_not_linked(): void
    {
        ProtectedArea::factory()->create(['boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01)]);

        $spot = FishingSpot::factory()->at(32.70, -16.90)->create();

        $this->assertNull($spot->protected_area_id);
    }

    public function test_spot_inside_bbox_but_outside_polygon_is_not_linked(): void
    {
        // Triângulo: o canto superior direito do retângulo envolvente fica fora do polígono
        ProtectedArea::factory()->create(['boundary' => [
            'type' => 'Polygon',
            'coordinates' => [[[-16.86, 32.63], [-16.84, 32.63], [-16.86, 32.65], [-16.86, 32.63]]],
        ]]);

        $spot = FishingSpot::factory()->at(32.648, -16.842)->create();

        $this->assertNull($spot->protected_area_id);
    }

    public function test_moving_spot_recalculates_protected_area(): void
    {
        $area = ProtectedArea::factory()->create(['boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01)]);
        $spot = FishingSpot::factory()->at(32.70, -16.90)->create();

        $spot->update(['latitude' => 32.641, 'longitude' => -16.851]);

        $this->assertSame($area->id, $spot->fresh()->protected_area_id);
    }

    public function test_nearest_forecast_point_on_same_coast_is_assigned(): void
    {
        $south = ForecastPoint::factory()->create(['latitude' => 32.625, 'longitude' => -16.910, 'coast' => Coast::Sul]);
        ForecastPoint::factory()->create(['latitude' => 32.645, 'longitude' => -16.915, 'coast' => Coast::Norte]); // mais perto, mas outra costa

        $spot = FishingSpot::factory()->at(32.6418, -16.9135)->create(['coast' => Coast::Sul]);

        $this->assertSame($south->id, $spot->forecast_point_id);
    }

    public function test_inactive_forecast_points_are_ignored(): void
    {
        $active = ForecastPoint::factory()->create(['latitude' => 32.70, 'longitude' => -16.80, 'coast' => Coast::Sul]);
        ForecastPoint::factory()->inactive()->create(['latitude' => 32.642, 'longitude' => -16.913, 'coast' => Coast::Sul]);

        $spot = FishingSpot::factory()->at(32.6418, -16.9135)->create(['coast' => Coast::Sul]);

        $this->assertSame($active->id, $spot->forecast_point_id);
    }

    public function test_manually_chosen_forecast_point_is_kept(): void
    {
        ForecastPoint::factory()->create(['latitude' => 32.642, 'longitude' => -16.913, 'coast' => Coast::Sul]);
        $chosen = ForecastPoint::factory()->create(['latitude' => 32.70, 'longitude' => -16.80, 'coast' => Coast::Sul]);

        $spot = FishingSpot::factory()->at(32.6418, -16.9135)->create(['forecast_point_id' => $chosen->id]);

        $this->assertSame($chosen->id, $spot->forecast_point_id);
    }

    public function test_invalid_coordinates_are_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FishingSpot::factory()->at(200, -16.9)->create();
    }

    public function test_invalid_access_difficulty_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        FishingSpot::factory()->create(['access_difficulty' => 9]);
    }

    public function test_published_at_is_set_when_published(): void
    {
        $spot = FishingSpot::factory()->draft()->create();
        $this->assertNull($spot->published_at);

        $spot->update(['status' => SpotStatus::Published]);

        $this->assertNotNull($spot->fresh()->published_at);
    }

    // ─── Seeders ────────────────────────────────────────────────────────

    public function test_full_database_seeder_links_garajau_spot_to_reserve(): void
    {
        $this->seed(DatabaseSeeder::class);

        $spot = FishingSpot::where('slug', 'calhau-do-garajau')->first();

        $this->assertSame('reserva-natural-parcial-garajau', $spot->protectedArea?->slug);
        $this->assertNotNull($spot->forecast_point_id);
        $this->assertTrue($spot->fishingTypes()->exists());
    }

    public function test_spot_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $count = FishingSpot::count();

        $this->seed(FishingSpotSeeder::class);

        $this->assertSame($count, FishingSpot::count());
    }

    // ─── API ────────────────────────────────────────────────────────────

    public function test_index_lists_only_published_spots_paginated(): void
    {
        FishingSpot::factory()->count(3)->create();
        FishingSpot::factory()->draft()->create();

        $this->getJson('/api/spots')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonStructure([
                'data' => [['slug', 'name', 'spot_type' => ['slug', 'name'], 'municipality' => ['slug', 'name'], 'coast', 'latitude', 'longitude', 'access_difficulty', 'in_protected_area']],
                'links', 'meta',
            ]);
    }

    public function test_index_respects_per_page_limit(): void
    {
        $this->getJson('/api/spots?per_page=500')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_index_filters_by_fishing_type(): void
    {
        $spinning = FishingType::create(['slug' => 'spinning', 'name' => 'Spinning']);
        FishingSpot::factory()->create(['slug' => 'com-spinning'])->fishingTypes()->attach($spinning);
        FishingSpot::factory()->create(['slug' => 'sem-spinning']);

        $this->getJson('/api/spots?fishing_type=spinning')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'com-spinning');
    }

    public function test_index_filters_by_coast_and_search(): void
    {
        FishingSpot::factory()->create(['name' => 'Cais do Norte', 'coast' => Coast::Norte]);
        FishingSpot::factory()->create(['name' => 'Molhe do Sul', 'coast' => Coast::Sul]);

        $this->getJson('/api/spots?coast=norte')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Cais do Norte');
        $this->getJson('/api/spots?q=MOLHE')->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Molhe do Sul');
    }

    public function test_index_filters_by_bbox(): void
    {
        FishingSpot::factory()->at(32.64, -16.91)->create(['slug' => 'funchal']);
        FishingSpot::factory()->at(33.06, -16.32)->create(['slug' => 'porto-santo']);

        // minLng,minLat,maxLng,maxLat
        $this->getJson('/api/spots?bbox=-17.0,32.6,-16.8,32.7')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'funchal');
    }

    public function test_index_rejects_unknown_filter_values(): void
    {
        $this->getJson('/api/spots?spot_type=nao-existe&bbox=lixo')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['spot_type', 'bbox']);
    }

    public function test_map_returns_geojson_feature_collection(): void
    {
        FishingSpot::factory()->at(32.6418, -16.9135)->create(['slug' => 'pontinha']);
        FishingSpot::factory()->draft()->create();

        $this->getJson('/api/spots/map')
            ->assertOk()
            ->assertJsonPath('data.type', 'FeatureCollection')
            ->assertJsonCount(1, 'data.features')
            ->assertJsonPath('data.features.0.geometry.coordinates', [-16.9135, 32.6418])
            ->assertJsonPath('data.features.0.properties.slug', 'pontinha');
    }

    public function test_show_returns_full_spot_with_relations(): void
    {
        $area = ProtectedArea::factory()->create(['boundary' => ProtectedAreaFactory::square(32.64, -16.85, 0.01)]);
        $spot = FishingSpot::factory()->at(32.641, -16.851)->create(['slug' => 'garajau']);
        $spot->fishingTypes()->attach(FishingType::create(['slug' => 'fundo', 'name' => 'Pesca de fundo']));

        $this->getJson('/api/spots/garajau')
            ->assertOk()
            ->assertJsonPath('data.protected_area.slug', $area->slug)
            ->assertJsonPath('data.fishing_types.0.slug', 'fundo')
            ->assertJsonPath('data.coast_label', 'Costa sul')
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.status');
    }

    public function test_show_hides_draft_and_deleted_spots(): void
    {
        FishingSpot::factory()->draft()->create(['slug' => 'rascunho']);
        FishingSpot::factory()->create(['slug' => 'apagado'])->delete();

        $this->getJson('/api/spots/rascunho')->assertNotFound()->assertJsonPath('code', 'not_found');
        $this->getJson('/api/spots/apagado')->assertNotFound();
    }

    public function test_map_route_is_not_captured_by_slug_route(): void
    {
        $this->getJson('/api/spots/map')->assertOk()->assertJsonPath('data.type', 'FeatureCollection');
    }
}
