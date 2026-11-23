<?php

namespace App\Http\Controllers;

use App\Http\Resources\FishingSpotSummaryResource;
use App\Http\Resources\SpeciesResource;
use App\Http\Resources\SpeciesSummaryResource;
use App\Models\Species;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class SpeciesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->validate([
                'q' => ['sometimes', 'string', 'min:2', 'max:100'],
                'month' => ['sometimes', 'integer', 'between:1,12'],
                'fishing_type' => ['sometimes', 'string', 'exists:fishing_types,slug'],
                'bait' => ['sometimes', 'string', 'exists:baits,slug'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            ]);

            $data = SpeciesSummaryResource::collection(
                Species::query()
                    ->published()
                    ->when($filters['q'] ?? null, function (Builder $query, string $term) {
                        $like = '%'.mb_strtolower($term).'%';

                        // Pesquisa no nome comum, científico e nos nomes locais/sinónimos
                        $query->where(fn (Builder $q) => $q
                            ->whereRaw('lower(common_name) like ?', [$like])
                            ->orWhereRaw('lower(scientific_name) like ?', [$like])
                            ->orWhereHas('names', fn (Builder $n) => $n->whereRaw('lower(name) like ?', [$like])));
                    })
                    ->when($filters['month'] ?? null, fn (Builder $q, int $month) => $q->whereHas('months', fn (Builder $m) => $m->where('month', $month)))
                    ->when($filters['fishing_type'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('fishingTypes', fn (Builder $t) => $t->where('slug', $slug)))
                    ->when($filters['bait'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('baits', fn (Builder $b) => $b->where('slug', $slug)))
                    ->orderBy('common_name')
                    ->paginate((int) ($filters['per_page'] ?? 20))
                    ->withQueryString()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(Species $species): JsonResponse
    {
        try {
            $data = new SpeciesResource(
                $species->load(['names', 'months', 'baits', 'fishingTypes', 'minLegalSizeRegulation'])
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function spots(Species $species): JsonResponse
    {
        try {
            $data = FishingSpotSummaryResource::collection(
                $species->spots()
                    ->published()
                    ->with(['spotType', 'municipality'])
                    ->orderBy('name')
                    ->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }
}
