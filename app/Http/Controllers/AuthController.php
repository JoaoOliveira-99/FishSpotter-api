<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = new User($data);
        $user->role = Role::User;
        $user->save();

        $token = $this->guard()->login($user);

        return $this->tokenResponse($token, $user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $this->normalizeEmail($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $token = $this->guard()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->tokenResponse($token, $this->guard()->user());
    }

    public function logout(): JsonResponse
    {
        $this->guard()->logout();

        return response()->json(['message' => __('auth.logged_out')]);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->guard()->refresh();

        return $this->tokenResponse($token, $this->guard()->setToken($token)->user());
    }

    public function me(): JsonResponse
    {
        return response()->json($this->guard()->user());
    }

    private function guard(): JWTGuard
    {
        /** @var JWTGuard */
        return auth('api');
    }

    private function tokenResponse(string $token, ?User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60,
        ], $status);
    }

    private function normalizeEmail(Request $request): void
    {
        if ($request->has('email')) {
            $request->merge([
                'email' => Str::lower(trim((string) $request->input('email'))),
            ]);
        }
    }
}
