<?php

namespace App\Http\Controllers;

use App\Enums\RestrictionLevel;
use App\Http\Resources\ProtectedAreaResource;
use App\Models\ProtectedArea;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class ProtectedAreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'restriction_level' => ['sometimes', 'string', Rule::enum(RestrictionLevel::class)],
            ]);

            $data = ProtectedAreaResource::collection(
                ProtectedArea::query()
                    ->with('regulation')
                    ->when(
                        $request->enum('restriction_level', RestrictionLevel::class),
                        fn ($query, RestrictionLevel $level) => $query->where('restriction_level', $level),
                    )
                    ->orderBy('name')
                    ->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(ProtectedArea $protectedArea): JsonResponse
    {
        try {
            $data = new ProtectedAreaResource($protectedArea->load('regulation'));

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
