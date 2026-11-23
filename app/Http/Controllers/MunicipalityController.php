<?php

namespace App\Http\Controllers;

use App\Enums\Island;
use App\Http\Resources\MunicipalityResource;
use App\Models\Municipality;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MunicipalityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'island' => ['sometimes', 'string', Rule::enum(Island::class)],
            ]);

            $data = MunicipalityResource::collection(
                Municipality::query()
                    ->when(
                        $request->enum('island', Island::class),
                        fn ($query, Island $island) => $query->where('island', $island),
                    )
                    ->orderBy('name')
                    ->get()
            );

            return ApiResponse::success($data, Response::HTTP_OK);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(Municipality $municipality): JsonResponse
    {
        try {
            $data = new MunicipalityResource($municipality);

            return ApiResponse::success($data, Response::HTTP_OK);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
