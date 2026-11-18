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
        // Dados de desenvolvimento (inclui o admin admin@admin.pt / 123) — nunca em produção.
        if (app()->isProduction()) {
            return;
        }

        $this->call([
            UserSeeder::class,
        ]);
    }
}
