<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
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
                LicenseTypeSeeder::class,
                ProtectedAreaSeeder::class,
                ForecastPointSeeder::class,
                FishingSpotSeeder::class,
                SpeciesSeeder::class,
            ]);
        }
    }
}
