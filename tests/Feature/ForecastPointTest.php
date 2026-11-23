<?php

namespace Tests\Feature;

use App\Enums\Coast;
use App\Models\ForecastPoint;
use Database\Seeders\ForecastPointSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastPointTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(ForecastPointSeeder::class);
        $count = ForecastPoint::count();

        $this->seed(ForecastPointSeeder::class);

        $this->assertGreaterThan(0, $count);
        $this->assertSame($count, ForecastPoint::count());
    }

    public function test_seeder_covers_all_coasts(): void
    {
        $this->seed(ForecastPointSeeder::class);

        foreach (Coast::cases() as $coast) {
            $this->assertTrue(
                ForecastPoint::where('coast', $coast)->exists(),
                "Falta um ponto de previsão para {$coast->value}.",
            );
        }
    }

    public function test_coordinates_are_cast_to_float(): void
    {
        $point = ForecastPoint::factory()->create(['latitude' => 32.625, 'longitude' => -16.91]);

        $this->assertSame(32.625, $point->fresh()->latitude);
        $this->assertSame(-16.91, $point->fresh()->longitude);
    }

    public function test_active_scope_excludes_inactive_points(): void
    {
        ForecastPoint::factory()->create();
        ForecastPoint::factory()->inactive()->create();

        $this->assertSame(1, ForecastPoint::active()->count());
    }
}
