<?php

namespace Database\Factories;

use App\Enums\RestrictionLevel;
use App\Models\ProtectedArea;
use App\Models\Regulation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProtectedArea>
 */
class ProtectedAreaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Área '.fake()->unique()->words(2, true);

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'boundary' => self::square(32.64, -16.85, 0.01),
            'restriction_level' => RestrictionLevel::Interdita,
            'restrictions' => fake()->sentence(),
            'regulation_id' => Regulation::factory(),
            'verified_at' => now(),
        ];
    }

    /**
     * Polígono quadrado GeoJSON à volta de um ponto (para testes).
     *
     * @return array<string, mixed>
     */
    public static function square(float $lat, float $lng, float $half): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [$lng - $half, $lat - $half],
                [$lng + $half, $lat - $half],
                [$lng + $half, $lat + $half],
                [$lng - $half, $lat + $half],
                [$lng - $half, $lat - $half],
            ]],
        ];
    }
}
