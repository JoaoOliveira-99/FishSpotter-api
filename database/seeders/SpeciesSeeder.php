<?php

namespace Database\Seeders;

use App\Enums\ContentStatus;
use App\Enums\SpeciesNameKind;
use App\Enums\SpotFrequency;
use App\Models\Bait;
use App\Models\FishingSpot;
use App\Models\FishingType;
use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('SpeciesSeeder ignorado em produção (dados de exemplo).');

            return;
        }

        // Espécies de EXEMPLO. Sem tamanhos mínimos legais nem sazonalidade:
        // esses dados entram só depois de verificados com a legislação.
        $species = [
            [
                'slug' => 'sargo',
                'common_name' => 'Sargo',
                'scientific_name' => 'Diplodus sargus',
                'family' => 'Sparidae',
                'names' => [['Sargo-legítimo', SpeciesNameKind::Comum], ['White seabream', SpeciesNameKind::En]],
                'fishing_types' => ['fundo', 'boia'],
                'baits' => ['camarao', 'caranguejo', 'lapa'],
                'spots' => ['molhe-da-pontinha' => SpotFrequency::Comum, 'cais-de-camara-de-lobos' => SpotFrequency::Ocasional],
            ],
            [
                'slug' => 'bodiao',
                'common_name' => 'Bodião',
                'scientific_name' => 'Sparisoma cretense',
                'family' => 'Scaridae',
                'names' => [['Peixe-papagaio', SpeciesNameKind::Comum], ['Parrotfish', SpeciesNameKind::En]],
                'fishing_types' => ['fundo', 'boia'],
                'baits' => ['camarao', 'massa-pao'],
                'spots' => ['molhe-da-pontinha' => SpotFrequency::Ocasional, 'porto-de-abrigo-do-porto-santo' => SpotFrequency::Comum],
            ],
            [
                'slug' => 'chicharro',
                'common_name' => 'Chicharro',
                'scientific_name' => 'Trachurus picturatus',
                'family' => 'Carangidae',
                'names' => [['Carapau-negrão', SpeciesNameKind::Comum], ['Blue jack mackerel', SpeciesNameKind::En]],
                'fishing_types' => ['boia', 'spinning'],
                'baits' => ['camarao', 'sardinha', 'colher'],
                'spots' => ['cais-de-camara-de-lobos' => SpotFrequency::Comum, 'cais-do-porto-moniz' => SpotFrequency::Ocasional],
            ],
            [
                'slug' => 'cavala',
                'common_name' => 'Cavala',
                'scientific_name' => 'Scomber colias',
                'family' => 'Scombridae',
                'names' => [['Atlantic chub mackerel', SpeciesNameKind::En]],
                'fishing_types' => ['spinning', 'boia'],
                'baits' => ['sardinha', 'colher', 'vinil'],
                'spots' => ['molhe-da-pontinha' => SpotFrequency::Ocasional, 'cais-do-porto-moniz' => SpotFrequency::Comum],
            ],
            [
                'slug' => 'pargo',
                'common_name' => 'Pargo',
                'scientific_name' => 'Pagrus pagrus',
                'family' => 'Sparidae',
                'names' => [['Red porgy', SpeciesNameKind::En]],
                'fishing_types' => ['fundo', 'jigging'],
                'baits' => ['lula', 'camarao', 'jig-metalico'],
                'spots' => ['cais-do-porto-moniz' => SpotFrequency::Rara],
            ],
            [
                'slug' => 'moreia',
                'common_name' => 'Moreia',
                'scientific_name' => 'Muraena helena',
                'family' => 'Muraenidae',
                'names' => [['Mediterranean moray', SpeciesNameKind::En]],
                'safety_notes' => 'Mordedura forte. Usar alicate para retirar o anzol e nunca aproximar as mãos da boca.',
                'fishing_types' => ['fundo'],
                'baits' => ['sardinha', 'lula'],
                'spots' => ['cais-de-camara-de-lobos' => SpotFrequency::Ocasional],
            ],
        ];

        foreach ($species as $data) {
            $model = Species::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'common_name' => $data['common_name'],
                    'scientific_name' => $data['scientific_name'],
                    'family' => $data['family'],
                    'description' => 'Espécie de exemplo para desenvolvimento.',
                    'safety_notes' => $data['safety_notes'] ?? null,
                    'status' => ContentStatus::Published,
                ],
            );

            $model->names()->delete();
            $model->names()->createMany(
                array_map(fn (array $n) => ['name' => $n[0], 'kind' => $n[1]], $data['names']),
            );

            $model->fishingTypes()->sync(FishingType::whereIn('slug', $data['fishing_types'])->pluck('id'));
            $model->baits()->sync(Bait::whereIn('slug', $data['baits'])->pluck('id'));

            $spots = FishingSpot::whereIn('slug', array_keys($data['spots']))->pluck('id', 'slug');
            $model->spots()->sync(
                $spots->mapWithKeys(fn (int $id, string $slug) => [$id => ['frequency' => $data['spots'][$slug]->value]])->all(),
            );
        }
    }
}
