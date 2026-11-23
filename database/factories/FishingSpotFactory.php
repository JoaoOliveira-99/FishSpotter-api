<?php

namespace Database\Factories;

use App\Enums\Coast;
use App\Enums\ContentStatus;
use App\Models\FishingSpot;
use App\Models\Municipality;
use App\Models\SpotType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FishingSpot>
 */
class FishingSpotFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Local '.fake()->unique()->words(3, true);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'description' => fake()->paragraph(),
            'spot_type_id' => fn () => SpotType::query()->firstOrCreate(['slug' => 'cais'], ['name' => 'Cais'])->id,
            'municipality_id' => fn () => Municipality::query()->firstOrCreate(['slug' => 'funchal'], ['name' => 'Funchal', 'island' => 'madeira'])->id,
            'latitude' => 32.641800,
            'longitude' => -16.913500,
            'coast' => Coast::Sul,
            'exposure_deg' => 180,
            'access_description' => fake()->sentence(),
            'access_difficulty' => 2,
            'has_parking' => true,
            'safety_notes' => fake()->sentence(),
            'status' => ContentStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Draft,
        ]);
    }

    public function at(float $lat, float $lng): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $lat,
            'longitude' => $lng,
        ]);
    }
}
