<?php

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Regulation;
use App\Models\Species;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Species>
 */
class SpeciesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $common = 'Peixe '.fake()->unique()->word().' '.fake()->unique()->numberBetween(1, 99999);
        $scientific = ucfirst(fake()->unique()->lexify('??????')).' '.fake()->lexify('??????');

        return [
            'slug' => Str::slug($common),
            'common_name' => $common,
            'scientific_name' => $scientific,
            'family' => 'Sparidae',
            'description' => fake()->paragraph(),
            'habitat' => fake()->sentence(),
            'depth_min_m' => 0,
            'depth_max_m' => 50,
            'max_size_cm' => 40,
            'min_legal_size_cm' => null,
            'min_legal_size_regulation_id' => null,
            'capture_prohibited' => false,
            'safety_notes' => null,
            'status' => ContentStatus::Published,
            'verified_at' => now(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContentStatus::Draft,
        ]);
    }

    public function withMinLegalSize(float $cm = 15.0): static
    {
        return $this->state(fn (array $attributes) => [
            'min_legal_size_cm' => $cm,
            'min_legal_size_regulation_id' => Regulation::factory(),
        ]);
    }
}
