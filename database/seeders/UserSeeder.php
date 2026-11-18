<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createDefaultAdmin();

        User::factory(20)->create();
        User::factory(5)->unverified()->create();
    }

    private function createDefaultAdmin(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@admin.pt']);

        $admin->name = 'Administrador';
        $admin->role = Role::Admin;
        $admin->email_verified_at ??= now();
        
        if (! $admin->exists) {
            $admin->password = '123';
        }

        $admin->save();
    }
}
