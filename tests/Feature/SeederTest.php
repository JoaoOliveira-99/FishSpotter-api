<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_seeder_creates_single_default_admin(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::where('role', Role::Admin)->count());
        $this->assertTrue(User::where('email', 'admin@admin.pt')->first()->isAdmin());
    }

    public function test_default_admin_can_login(): void
    {
        $this->seed(UserSeeder::class);

        $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.pt',
            'password' => '123',
        ])->assertOk()->assertJsonPath('user.role', 'admin');
    }

    public function test_factory_admin_state(): void
    {
        $this->assertTrue(User::factory()->admin()->create()->isAdmin());
    }
}
