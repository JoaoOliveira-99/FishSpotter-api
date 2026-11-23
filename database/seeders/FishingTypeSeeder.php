<?php

namespace Database\Seeders;

use App\Enums\LicenseModality;
use App\Models\FishingType;
use Illuminate\Database\Seeder;

class FishingTypeSeeder extends Seeder
{
    public function run(): void
    {
        // license_modality só é preenchido quando a técnica implica uma modalidade de licença
        $fishingTypes = [
            [
                'slug' => 'fundo',
                'name' => 'Pesca de fundo',
                'description' => 'Isco junto ao fundo, com chumbo, à espera do toque.',
                'license_modality' => null,
            ],
            [
                'slug' => 'boia',
                'name' => 'Pesca à bóia',
                'description' => 'Isco suspenso a meia-água por uma bóia que sinaliza o toque.',
                'license_modality' => null,
            ],
            [
                'slug' => 'spinning',
                'name' => 'Spinning',
                'description' => 'Lançamento e recolha contínua de amostras artificiais.',
                'license_modality' => null,
            ],
            [
                'slug' => 'jigging',
                'name' => 'Jigging',
                'description' => 'Amostras metálicas pesadas trabalhadas na vertical.',
                'license_modality' => null,
            ],
            [
                'slug' => 'corrico',
                'name' => 'Corrico',
                'description' => 'Amostras ou isco arrastados atrás de uma embarcação em movimento.',
                'license_modality' => LicenseModality::Embarcada,
            ],
            [
                'slug' => 'submarina',
                'name' => 'Pesca submarina',
                'description' => 'Pesca em apneia com arpão.',
                'license_modality' => LicenseModality::Submarina,
            ],
        ];

        foreach ($fishingTypes as $data) {
            FishingType::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }
}
