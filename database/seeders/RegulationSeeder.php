<?php

namespace Database\Seeders;

use App\Models\Regulation;
use Illuminate\Database\Seeder;

class RegulationSeeder extends Seeder
{
    private const SIMPLIFICA_URL = 'https://simplifica.madeira.gov.pt/services/8-26-1';

    private const PROVISIONAL_SUMMARY = 'Dados provisórios para desenvolvimento. Confirmar no JORAM antes de publicar.';

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('RegulationSeeder ignorado em produção (dados provisórios).');

            return;
        }

        $regulations = [
            [
                'slug' => 'dlr-19-2016-m',
                'reference' => 'Decreto Legislativo Regional n.º 19/2016/M',
                'title' => 'Pesca lúdica na Região Autónoma da Madeira',
                'url' => self::SIMPLIFICA_URL,
            ],
            [
                'slug' => 'portaria-484-2016',
                'reference' => 'Portaria n.º 484/2016',
                'title' => 'Artes permitidas, condicionalismos e licenciamento da pesca lúdica',
                'url' => self::SIMPLIFICA_URL,
            ],
            [
                'slug' => 'portaria-384-2019',
                'reference' => 'Portaria n.º 384/2019',
                'title' => 'Alteração à Portaria n.º 484/2016',
                'url' => self::SIMPLIFICA_URL,
            ],
            [
                'slug' => 'declaracao-retificacao-14-2019',
                'reference' => 'Declaração de Retificação n.º 14/2019',
                'title' => 'Retificação da Portaria n.º 484/2016',
                'url' => self::SIMPLIFICA_URL,
            ],
            [
                'slug' => 'portaria-609-2016',
                'reference' => 'Portaria n.º 609/2016',
                'title' => 'Taxas de licenciamento da pesca lúdica',
                'url' => self::SIMPLIFICA_URL,
            ],
            [
                'slug' => 'dlr-23-86-m',
                'reference' => 'Decreto Legislativo Regional n.º 23/86/M',
                'title' => 'Reserva Natural Parcial do Garajau',
                'url' => null,
            ],
        ];

        foreach ($regulations as $data) {
            Regulation::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'summary' => self::PROVISIONAL_SUMMARY,
                    'published_on' => null,
                    'verified_at' => now(),
                ],
            );
        }
    }
}
