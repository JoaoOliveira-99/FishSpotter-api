<?php

namespace Database\Seeders;

use App\Enums\Coast;
use App\Enums\SpotStatus;
use App\Models\FishingSpot;
use App\Models\FishingType;
use App\Models\Municipality;
use App\Models\SpotType;
use Illuminate\Database\Seeder;

class FishingSpotSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('FishingSpotSeeder ignorado em produção (locais de exemplo).');

            return;
        }

        // Locais de EXEMPLO com coordenadas aproximadas, só para desenvolvimento
        $spots = [
            [
                'slug' => 'molhe-da-pontinha',
                'name' => 'Molhe da Pontinha',
                'spot_type' => 'molhe',
                'municipality' => 'funchal',
                'latitude' => 32.641800,
                'longitude' => -16.913500,
                'coast' => Coast::Sul,
                'exposure_deg' => 180,
                'access_difficulty' => 1,
                'has_parking' => true,
                'fishing_types' => ['fundo', 'boia', 'spinning'],
            ],
            [
                'slug' => 'cais-de-camara-de-lobos',
                'name' => 'Cais de Câmara de Lobos',
                'spot_type' => 'cais',
                'municipality' => 'camara-de-lobos',
                'latitude' => 32.647500,
                'longitude' => -16.976500,
                'coast' => Coast::Sul,
                'exposure_deg' => 170,
                'access_difficulty' => 1,
                'has_parking' => true,
                'fishing_types' => ['fundo', 'boia'],
            ],
            [
                'slug' => 'calhau-do-garajau',
                'name' => 'Calhau do Garajau',
                'spot_type' => 'calhau',
                'municipality' => 'santa-cruz',
                'latitude' => 32.638000,
                'longitude' => -16.855000,
                'coast' => Coast::Sul,
                'exposure_deg' => 160,
                'access_difficulty' => 4,
                'has_parking' => false,
                'fishing_types' => ['fundo'],
            ],
            [
                'slug' => 'cais-do-porto-moniz',
                'name' => 'Cais do Porto Moniz',
                'spot_type' => 'cais',
                'municipality' => 'porto-moniz',
                'latitude' => 32.867800,
                'longitude' => -17.169000,
                'coast' => Coast::Norte,
                'exposure_deg' => 0,
                'access_difficulty' => 2,
                'has_parking' => true,
                'fishing_types' => ['spinning', 'jigging'],
            ],
            [
                'slug' => 'porto-de-abrigo-do-porto-santo',
                'name' => 'Porto de Abrigo do Porto Santo',
                'spot_type' => 'porto',
                'municipality' => 'porto-santo',
                'latitude' => 33.058000,
                'longitude' => -16.316000,
                'coast' => Coast::PortoSanto,
                'exposure_deg' => 135,
                'access_difficulty' => 1,
                'has_parking' => true,
                'fishing_types' => ['fundo', 'boia', 'spinning'],
            ],
        ];

        foreach ($spots as $data) {
            $spot = FishingSpot::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => 'Local de exemplo para desenvolvimento.',
                    'spot_type_id' => SpotType::where('slug', $data['spot_type'])->value('id'),
                    'municipality_id' => Municipality::where('slug', $data['municipality'])->value('id'),
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'coast' => $data['coast'],
                    'exposure_deg' => $data['exposure_deg'],
                    'access_difficulty' => $data['access_difficulty'],
                    'has_parking' => $data['has_parking'],
                    'status' => SpotStatus::Published,
                ],
            );

            $spot->fishingTypes()->sync(
                FishingType::whereIn('slug', $data['fishing_types'])->pluck('id'),
            );
        }
    }
}
