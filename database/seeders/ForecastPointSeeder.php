<?php

namespace Database\Seeders;

use App\Enums\Coast;
use App\Models\ForecastPoint;
use Illuminate\Database\Seeder;

class ForecastPointSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('ForecastPointSeeder ignorado em produção (posições aproximadas).');

            return;
        }

        // Posições APROXIMADAS no mar, junto à costa. Códigos de aviso do IPMA por confirmar.
        $points = [
            ['name' => 'Funchal',      'latitude' => 32.625000, 'longitude' => -16.910000, 'coast' => Coast::Sul,        'warning_area_code' => 'MCS'],
            ['name' => 'Câmara de Lobos', 'latitude' => 32.635000, 'longitude' => -16.975000, 'coast' => Coast::Sul,     'warning_area_code' => 'MCS'],
            ['name' => 'Calheta',      'latitude' => 32.710000, 'longitude' => -17.180000, 'coast' => Coast::Sul,        'warning_area_code' => 'MCS'],
            ['name' => 'Machico',      'latitude' => 32.710000, 'longitude' => -16.745000, 'coast' => Coast::Sul,        'warning_area_code' => 'MCS'],
            ['name' => 'Porto Moniz',  'latitude' => 32.880000, 'longitude' => -17.165000, 'coast' => Coast::Norte,      'warning_area_code' => 'MCN'],
            ['name' => 'São Vicente',  'latitude' => 32.815000, 'longitude' => -17.045000, 'coast' => Coast::Norte,      'warning_area_code' => 'MCN'],
            ['name' => 'Santana',      'latitude' => 32.830000, 'longitude' => -16.880000, 'coast' => Coast::Norte,      'warning_area_code' => 'MCN'],
            ['name' => 'Porto Santo',  'latitude' => 33.045000, 'longitude' => -16.335000, 'coast' => Coast::PortoSanto, 'warning_area_code' => 'MPS'],
        ];

        foreach ($points as $data) {
            ForecastPoint::updateOrCreate(
                ['name' => $data['name']],
                $data,
            );
        }
    }
}
