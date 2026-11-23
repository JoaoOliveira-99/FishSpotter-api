<?php

namespace App\Http\Controllers;

use App\Enums\Coast;
use App\Http\Resources\FishingSpotResource;
use App\Http\Resources\FishingSpotSummaryResource;
use App\Http\Resources\SpeciesSummaryResource;
use App\Models\FishingSpot;
use App\Support\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class FishingSpotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $this->validateFilters($request);

            $data = FishingSpotSummaryResource::collection(
                $this->filteredQuery($filters)
                    ->with(['spotType', 'municipality'])
                    ->orderBy('name')
                    ->paginate(min((int) ($filters['per_page'] ?? 20), 50))
                    ->withQueryString()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function map(Request $request): JsonResponse
    {
        try {
            $filters = $this->validateFilters($request);

            $spots = $this->filteredQuery($filters)
                ->with('spotType:id,slug')
                ->get(['id', 'slug', 'name', 'spot_type_id', 'latitude', 'longitude', 'coast', 'protected_area_id']);

            // GeoJSON FeatureCollection, pronto para o flutter_map
            $data = [
                'type' => 'FeatureCollection',
                'features' => $spots->map(fn (FishingSpot $spot) => [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$spot->longitude, $spot->latitude],
                    ],
                    'properties' => [
                        'slug' => $spot->slug,
                        'name' => $spot->name,
                        'spot_type' => $spot->spotType->slug,
                        'coast' => $spot->coast->value,
                        'in_protected_area' => $spot->protected_area_id !== null,
                    ],
                ])->values()->all(),
            ];

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function show(FishingSpot $fishingSpot): JsonResponse
    {
        try {
            $data = new FishingSpotResource(
                $fishingSpot->load(['spotType', 'municipality', 'fishingTypes', 'protectedArea'])
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    public function species(FishingSpot $fishingSpot): JsonResponse
    {
        try {
            $data = SpeciesSummaryResource::collection(
                $fishingSpot->species()
                    ->published()
                    ->orderBy('common_name')
                    ->get()
            );

            return ApiResponse::success($data, 200);
        } catch (Throwable $e) {
            return ApiResponse::exception($e);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'fishing_type' => ['sometimes', 'string', 'exists:fishing_types,slug'],
            'spot_type' => ['sometimes', 'string', 'exists:spot_types,slug'],
            'municipality' => ['sometimes', 'string', 'exists:municipalities,slug'],
            'coast' => ['sometimes', 'string', Rule::enum(Coast::class)],
            'q' => ['sometimes', 'string', 'min:2', 'max:100'],
            'bbox' => ['sometimes', 'string', 'regex:/^-?\d+(\.\d+)?(,-?\d+(\.\d+)?){3}$/'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<FishingSpot>
     */
    private function filteredQuery(array $filters): Builder
    {
        return FishingSpot::query()
            ->published()
            ->when($filters['fishing_type'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('fishingTypes', fn (Builder $t) => $t->where('slug', $slug)))
            ->when($filters['spot_type'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('spotType', fn (Builder $t) => $t->where('slug', $slug)))
            ->when($filters['municipality'] ?? null, fn (Builder $q, string $slug) => $q->whereHas('municipality', fn (Builder $t) => $t->where('slug', $slug)))
            ->when($filters['coast'] ?? null, fn (Builder $q, string $coast) => $q->where('coast', $coast))
            ->when($filters['q'] ?? null, fn (Builder $q, string $term) => $q->whereRaw('lower(name) like ?', ['%'.mb_strtolower($term).'%']))
            ->when($filters['bbox'] ?? null, function (Builder $q, string $bbox) {

            [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', explode(',', $bbox));

                $q->whereBetween('latitude', [$minLat, $maxLat])
                    ->whereBetween('longitude', [$minLng, $maxLng]);
            });
    }
}
