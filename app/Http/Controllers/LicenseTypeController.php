<?php

namespace App\Http\Controllers;

use App\Enums\LicenseModality;
use App\Http\Resources\LicenseTypeResource;
use App\Models\LicenseType;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class LicenseTypeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'modality' => ['sometimes', 'string', Rule::enum(LicenseModality::class)],
            ]);

            $data = LicenseTypeResource::collection(
                LicenseType::query()
                    ->with('regulation')
                    ->when(
                        $request->enum('modality', LicenseModality::class),
                        fn ($query, LicenseModality $modality) => $query->where('modality', $modality),
                    )
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(LicenseType $licenseType): JsonResponse
    {
        try {
            $data = new LicenseTypeResource($licenseType->load('regulation'));

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
