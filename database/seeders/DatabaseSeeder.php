<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MunicipalitySeeder::class,
            SpotTypeSeeder::class,
            FishingTypeSeeder::class,
            BaitSeeder::class,
        ]);

        if (app()->environment(['local', 'staging', 'testing'])) {
            $this->call([
                UserSeeder::class,
                RegulationSeeder::class,
            ]);
        }
    }
}
