<?php

namespace Database\Seeders;

use App\Enums\LicenseModality;
use App\Models\LicenseType;
use App\Models\Regulation;
use Illuminate\Database\Seeder;

class LicenseTypeSeeder extends Seeder
{
    private const SIMPLIFICA_URL = 'https://simplifica.madeira.gov.pt/services/8-26-1';

    private const PROVISIONAL_NOTE = 'Dados provisórios para desenvolvimento. Confirmar com a Direção Regional de Pescas antes de publicar.';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('LicenseTypeSeeder ignorado em produção (dados provisórios).');

            return;
        }

        $regulationId = Regulation::where('slug', 'portaria-484-2016')->value('id');

        $requirements = [
            'Pedido no Portal SIMplifica, na Lota do Funchal ou na Direção Regional de Pescas.',
            'As licenças emitidas no continente não são válidas na Madeira.',
        ];

        $licenseTypes = [
            [
                'slug' => 'pesca-apeada',
                'name' => 'Licença de pesca lúdica apeada',
                'modality' => LicenseModality::Apeada,
                'sort_order' => 1,
            ],
            [
                'slug' => 'pesca-embarcada',
                'name' => 'Licença de pesca lúdica embarcada',
                'modality' => LicenseModality::Embarcada,
                'sort_order' => 2,
            ],
            [
                'slug' => 'pesca-submarina',
                'name' => 'Licença de pesca lúdica submarina',
                'modality' => LicenseModality::Submarina,
                'sort_order' => 3,
            ],
        ];

        foreach ($licenseTypes as $data) {
            LicenseType::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'summary' => self::PROVISIONAL_NOTE,
                    'requirements' => $requirements,
                    'validity_info' => null,
                    'fee_info' => null,
                    'official_url' => self::SIMPLIFICA_URL,
                    'regulation_id' => $regulationId,
                    'verified_at' => now(),
                ],
            );
        }
    }
}
