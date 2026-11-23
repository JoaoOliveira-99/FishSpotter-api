<?php

namespace App\Http\Resources;

use App\Models\FishingSpot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FishingSpot
 */
class FishingSpotSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'spot_type' => new SpotTypeResource($this->whenLoaded('spotType')),
            'municipality' => new MunicipalityResource($this->whenLoaded('municipality')),
            'coast' => $this->coast->value,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'access_difficulty' => $this->access_difficulty,
            'in_protected_area' => $this->protected_area_id !== null,
            // Só em /species/{slug}/spots
            'frequency' => $this->whenPivotLoaded('spot_species', fn () => $this->pivot->frequency),
        ];
    }
}
