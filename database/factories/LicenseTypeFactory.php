<?php

namespace Database\Factories;

use App\Enums\LicenseModality;
use App\Models\LicenseType;
use App\Models\Regulation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<LicenseType>
 */
class LicenseTypeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Licença '.fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'modality' => fake()->randomElement(LicenseModality::cases()),
            'summary' => fake()->paragraph(),
            'requirements' => [fake()->sentence(), fake()->sentence()],
            'validity_info' => fake()->sentence(),
            'fee_info' => fake()->sentence(),
            'official_url' => fake()->url(),
            'regulation_id' => Regulation::factory(),
            'sort_order' => 0,
            'verified_at' => now(),
        ];
    }

    public function withoutRegulation(): static
    {
        return $this->state(fn (array $attributes) => [
            'regulation_id' => null,
        ]);
    }
}
