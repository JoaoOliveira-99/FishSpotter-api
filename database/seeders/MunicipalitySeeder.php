<?php

namespace Database\Seeders;

use App\Enums\Island;
use App\Models\Municipality;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MunicipalitySeeder extends Seeder
{
    public function run(): void
    {
        $municipalities = [
            ['name' => 'Calheta',        'island' => Island::Madeira],
            ['name' => 'Câmara de Lobos', 'island' => Island::Madeira],
            ['name' => 'Funchal',        'island' => Island::Madeira],
            ['name' => 'Machico',        'island' => Island::Madeira],
            ['name' => 'Ponta do Sol',   'island' => Island::Madeira],
            ['name' => 'Porto Moniz',    'island' => Island::Madeira],
            ['name' => 'Ribeira Brava',  'island' => Island::Madeira],
            ['name' => 'Santa Cruz',     'island' => Island::Madeira],
            ['name' => 'Santana',        'island' => Island::Madeira],
            ['name' => 'São Vicente',    'island' => Island::Madeira],
            ['name' => 'Porto Santo',    'island' => Island::PortoSanto],
        ];

        foreach ($municipalities as $data) {
            Municipality::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                $data,
            );
        }
    }
}
