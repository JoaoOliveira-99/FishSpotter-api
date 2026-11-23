<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpotTypeResource;
use App\Models\SpotType;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SpotTypeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = SpotTypeResource::collection(
                SpotType::query()->orderBy('name')->get()
            );

            return ApiResponse::success($data, Response::HTTP_OK);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(SpotType $spotType): JsonResponse
    {
        try {
            $data = new SpotTypeResource($spotType);

            return ApiResponse::success($data, Response::HTTP_OK);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
