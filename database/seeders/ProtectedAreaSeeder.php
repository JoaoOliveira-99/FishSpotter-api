<?php

namespace Database\Seeders;

use App\Enums\RestrictionLevel;
use App\Models\ProtectedArea;
use App\Models\Regulation;
use Illuminate\Database\Seeder;

class ProtectedAreaSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('ProtectedAreaSeeder ignorado em produção (polígonos aproximados).');

            return;
        }

        Regulation::updateOrCreate(
            ['slug' => 'dlr-23-86-m'],
            [
                'reference' => 'Decreto Legislativo Regional n.º 23/86/M',
                'title' => 'Reserva Natural Parcial do Garajau',
                'summary' => 'Dados provisórios para desenvolvimento. Confirmar no JORAM antes de publicar.',
                'verified_at' => now(),
            ],
        );

        // Polígono APROXIMADO, só para desenvolvimento. Substituir pelos limites oficiais do IFCN (GeoJSON).
        ProtectedArea::updateOrCreate(
            ['slug' => 'reserva-natural-parcial-garajau'],
            [
                'name' => 'Reserva Natural Parcial do Garajau',
                'boundary' => [
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [-16.875, 32.627],
                        [-16.835, 32.627],
                        [-16.835, 32.645],
                        [-16.875, 32.645],
                        [-16.875, 32.627],
                    ]],
                ],
                'restriction_level' => RestrictionLevel::Interdita,
                'restrictions' => 'Pesca interdita em toda a área da reserva. Limites aproximados, por confirmar com o IFCN.',
                'regulation_id' => Regulation::where('slug', 'dlr-23-86-m')->value('id'),
                'verified_at' => now(),
            ],
        );
    }
}
