<?php

namespace Database\Factories;

use App\Enums\Coast;
use App\Models\ForecastPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ForecastPoint>
 */
class ForecastPointFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'latitude' => fake()->randomFloat(6, 32.60, 32.90),
            'longitude' => fake()->randomFloat(6, -17.30, -16.65),
            'coast' => fake()->randomElement(Coast::cases()),
            'warning_area_code' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
