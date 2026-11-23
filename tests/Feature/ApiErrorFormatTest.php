<?php

namespace Tests\Feature;

use Database\Seeders\MunicipalitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApiErrorFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_model_returns_standard_not_found(): void
    {
        $this->seed(MunicipalitySeeder::class);

        // assertExactJson garante que não sai o nome da classe nem o stack trace
        $this->getJson('/api/municipalities/nao-existe')
            ->assertNotFound()
            ->assertExactJson([
                'message' => __('errors.not_found'),
                'code' => 'not_found',
            ]);
    }

    public function test_unknown_route_returns_standard_not_found(): void
    {
        $this->getJson('/api/nao-existe')
            ->assertNotFound()
            ->assertExactJson([
                'message' => __('errors.not_found'),
                'code' => 'not_found',
            ]);
    }

    public function test_validation_error_returns_standard_format(): void
    {
        $this->getJson('/api/municipalities?island=acores')
            ->assertUnprocessable()
            ->assertJsonPath('message', __('errors.validation_failed'))
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonValidationErrors('island');
    }

    public function test_missing_token_returns_unauthenticated(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => __('errors.unauthenticated'),
                'code' => 'unauthenticated',
            ]);
    }

    public function test_wrong_method_returns_method_not_allowed(): void
    {
        $this->deleteJson('/api/municipalities')
            ->assertStatus(405)
            ->assertJsonPath('code', 'method_not_allowed');
    }

    public function test_unexpected_error_returns_server_error_without_details(): void
    {
        // Simula uma falha da BD dentro do try/catch do controller
        Schema::drop('spot_types');

        $this->getJson('/api/spot-types')
            ->assertStatus(500)
            ->assertExactJson([
                'message' => __('errors.server_error'),
                'code' => 'server_error',
            ]);
    }

    public function test_messages_are_translated(): void
    {
        // Falha se a chave não existir em lang/pt/errors.php (o __() devolve a própria chave)
        $this->assertSame('O recurso pedido não existe.', __('errors.not_found'));
    }
}
