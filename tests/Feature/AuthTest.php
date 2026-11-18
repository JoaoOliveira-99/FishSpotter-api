<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_returns_token(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Teste',
            'email' => 'teste@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()->assertJsonStructure(['user', 'token', 'token_type', 'expires_in']);
        $this->assertSame(Role::User, User::first()->role);
    }

    public function test_login_me_refresh_logout_flow(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'password123']);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'a@example.com',
            'password' => 'password123',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()->assertJsonPath('email', 'a@example.com');

        $newToken = $this->withToken($token)->postJson('/api/auth/refresh')->assertOk()->json('token');
        $this->assertNotSame($token, $newToken);

        $this->withToken($newToken)->postJson('/api/auth/logout')->assertOk();

        $this->app['auth']->forgetGuards();
        $this->withToken($newToken)->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->postJson('/api/auth/login', [
            'email' => 'a@example.com',
            'password' => 'errada',
        ])->assertUnprocessable();
    }

    public function test_me_requires_token(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_refresh_without_token_returns_401(): void
    {
        $this->postJson('/api/auth/refresh')->assertUnauthorized();
    }

    public function test_login_ignores_email_case(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'password123']);

        $this->postJson('/api/auth/login', [
            'email' => 'A@Example.com',
            'password' => 'password123',
        ])->assertOk();
    }
}
