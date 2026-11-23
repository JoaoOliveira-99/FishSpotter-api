<?php

namespace App\Http\Controllers;

use App\Http\Resources\FishingTypeResource;
use App\Models\FishingType;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Throwable;

class FishingTypeController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = FishingTypeResource::collection(
                FishingType::query()->orderBy('name')->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(FishingType $fishingType): JsonResponse
    {
        try {
            $data = new FishingTypeResource($fishingType);

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
