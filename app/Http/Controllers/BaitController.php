<?php

namespace App\Http\Controllers;

use App\Enums\BaitKind;
use App\Http\Resources\BaitResource;
use App\Models\Bait;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class BaitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'kind' => ['sometimes', 'string', Rule::enum(BaitKind::class)],
            ]);

            $data = BaitResource::collection(
                Bait::query()
                    ->when(
                        $request->enum('kind', BaitKind::class),
                        fn ($query, BaitKind $kind) => $query->where('kind', $kind),
                    )
                    ->orderBy('name')
                    ->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(Bait $bait): JsonResponse
    {
        try {
            $data = new BaitResource($bait);

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
