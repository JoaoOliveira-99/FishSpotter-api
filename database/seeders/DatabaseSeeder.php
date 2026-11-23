<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MunicipalitySeeder::class,
        ]);

        if (App::environment(['local', 'staging'])) {
            $this->call([
                UserSeeder::class,
                MunicipalitySeeder::class
            ]);
        }
    }
}
