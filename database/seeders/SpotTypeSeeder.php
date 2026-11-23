<?php

namespace Database\Seeders;

use App\Models\SpotType;
use Illuminate\Database\Seeder;

class SpotTypeSeeder extends Seeder
{
    public function run(): void
    {
        $spotTypes = [
            ['slug' => 'cais',          'name' => 'Cais'],
            ['slug' => 'molhe',         'name' => 'Molhe'],
            ['slug' => 'porto',         'name' => 'Porto'],
            ['slug' => 'marina',        'name' => 'Marina'],
            ['slug' => 'costa-rochosa', 'name' => 'Costa rochosa'],
            ['slug' => 'calhau',        'name' => 'Calhau'],
            ['slug' => 'praia',         'name' => 'Praia'],
        ];

        foreach ($spotTypes as $data) {
            SpotType::updateOrCreate(
                ['slug' => $data['slug']],
                $data,
            );
        }
    }
}
