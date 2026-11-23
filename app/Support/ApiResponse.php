<?php

namespace App\Support;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Respostas JSON da API num formato único.
 *
 *   Sucesso: { "data": ... }
 *   Erro:    { "message": "...", "code": "...", "errors": { ... } }
 *
 * Os textos de erro estão em lang/{locale}/errors.php, indexados pelo "code".
 */
final class ApiResponse
{
    /**
     * Exceções "esperadas": seguem para o ApiExceptionRenderer, que as converte
     * no status certo (401, 403, 404, 422…). Não podem virar 500.
     */
    private const PASSTHROUGH = [
        HttpExceptionInterface::class,
        ValidationException::class,
        AuthenticationException::class,
        AuthorizationException::class,
        ModelNotFoundException::class,
        JWTException::class,
    ];

    /**
     * @param  JsonResource|array<mixed>  $data
     */
    public static function success(JsonResource|array $data, int $status = 200): JsonResponse
    {
        if ($data instanceof JsonResource) {
            return $data->response()->setStatusCode($status);
        }

        return response()->json(['data' => $data], $status);
    }

    /**
     * @param  array<string, array<int, string>>|null  $errors
     * @param  array<string, string>  $headers
     */
    public static function error(string $code, int $status, ?array $errors = null, array $headers = []): JsonResponse
    {
        $body = [
            'message' => __("errors.{$code}"),
            'code' => $code,
        ];

        if ($errors !== null) {
            $body['errors'] = $errors;
        }

        return response()->json($body, $status, $headers);
    }

    /**
     * Para usar no catch dos controllers.
     *
     * Erros esperados são relançados para o ApiExceptionRenderer.
     * Erros inesperados (BD em baixo, bug…) são registados no log e devolvidos
     * como 500 sem detalhes internos.
     *
     * @throws Throwable
     */
    public static function exception(Throwable $e): JsonResponse
    {
        foreach (self::PASSTHROUGH as $class) {
            if ($e instanceof $class) {
                throw $e;
            }
        }

        report($e);

        return self::error('server_error', 500);
    }
}
