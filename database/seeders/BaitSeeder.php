<?php

namespace Database\Seeders;

use App\Enums\BaitKind;
use App\Models\Bait;
use Illuminate\Database\Seeder;

class BaitSeeder extends Seeder
{
    public function run(): void
    {
        $baits = [
            // Naturais
            ['slug' => 'camarao',            'name' => 'Camarão',               'kind' => BaitKind::Natural],
            ['slug' => 'lula',               'name' => 'Lula',                  'kind' => BaitKind::Natural],
            ['slug' => 'sardinha',           'name' => 'Sardinha',              'kind' => BaitKind::Natural],
            ['slug' => 'cavala',             'name' => 'Cavala',                'kind' => BaitKind::Natural],
            ['slug' => 'chicharro',          'name' => 'Chicharro',             'kind' => BaitKind::Natural],
            ['slug' => 'lapa',               'name' => 'Lapa',                  'kind' => BaitKind::Natural],
            ['slug' => 'caranguejo',         'name' => 'Caranguejo',            'kind' => BaitKind::Natural],
            ['slug' => 'minhoca-mar',        'name' => 'Minhoca do mar',        'kind' => BaitKind::Natural],
            ['slug' => 'massa-pao',          'name' => 'Massa de pão',          'kind' => BaitKind::Natural],

            // Artificiais
            ['slug' => 'vinil',              'name' => 'Vinil',                 'kind' => BaitKind::Artificial],
            ['slug' => 'amostra-superficie', 'name' => 'Amostra de superfície', 'kind' => BaitKind::Artificial],
            ['slug' => 'amostra-meia-agua',  'name' => 'Amostra de meia-água',  'kind' => BaitKind::Artificial],
            ['slug' => 'jig-metalico',       'name' => 'Jig metálico',          'kind' => BaitKind::Artificial],
            ['slug' => 'colher',             'name' => 'Colher',                'kind' => BaitKind::Artificial],
            ['slug' => 'polvo-corrico',      'name' => 'Polvo de corrico',      'kind' => BaitKind::Artificial],
        ];

        foreach ($baits as $data) {
            Bait::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }
}
