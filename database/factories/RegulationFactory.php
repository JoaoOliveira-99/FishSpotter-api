<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RegulationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999);
        $year = fake()->numberBetween(2000, 2026);

        return [
            'slug' => "portaria-{$number}-{$year}",
            'reference' => "Portaria n.º {$number}/{$year}",
            'title' => fake()->sentence(8),
            'summary' => fake()->paragraph(),
            'url' => fake()->url(),
            'published_on' => fake()->dateTimeBetween("{$year}-01-01", "{$year}-12-31"),
            'verified_at' => now(),
        ];
    }

    public function stale(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subMonths(7),
        ]);
    }
}
