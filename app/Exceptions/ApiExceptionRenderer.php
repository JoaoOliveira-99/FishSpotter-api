<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Formato único de erros da API (proposta, secção 8.2):
 *
 *   { "message": "...", "code": "...", "errors": { ... } }
 *
 * Os textos estão em lang/{locale}/errors.php, indexados pelo "code".
 */
class ApiExceptionRenderer
{
    /**
     * Códigos HTTP com "code" próprio. Os restantes usam "http_error".
     */
    private const HTTP_CODES = [
        403 => 'forbidden',
        404 => 'not_found',
        405 => 'method_not_allowed',
        429 => 'too_many_requests',
    ];

    public static function register(Exceptions $exceptions): void
    {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => self::isApi($request) || $request->expectsJson(),
        );

        // 422 — dados inválidos
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return ApiResponse::error('validation_failed', $e->status, errors: $e->errors());
        });

        // 401 — pedido sem sessão
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return ApiResponse::error('unauthenticated', 401);
        });

        // 401 — token JWT inválido, expirado ou em falta no refresh
        $exceptions->render(function (JWTException $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            return ApiResponse::error('invalid_token', 401);
        });

        // 403, 404, 405, 429… — inclui o 404 do route model binding
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if (! self::isApi($request)) {
                return null;
            }

            $status = $e->getStatusCode();

            // 5xx ficam com a resposta por omissão do Laravel (sem detalhes com APP_DEBUG=false)
            if ($status >= 500) {
                return null;
            }

            return ApiResponse::error(
                self::HTTP_CODES[$status] ?? 'http_error',
                $status,
                headers: $e->getHeaders(), // mantém, por exemplo, o Retry-After do 429
            );
        });
    }

    private static function isApi(Request $request): bool
    {
        return $request->is('api/*');
    }
}
