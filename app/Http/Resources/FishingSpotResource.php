<?php

namespace App\Http\Resources;

use App\Models\FishingSpot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Versão completa, para o detalhe do local.
 *
 * @mixin FishingSpot
 */
class FishingSpotResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'spot_type' => new SpotTypeResource($this->whenLoaded('spotType')),
            'municipality' => new MunicipalityResource($this->whenLoaded('municipality')),
            'coast' => $this->coast->value,
            'coast_label' => $this->coast->label(),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'exposure_deg' => $this->exposure_deg,
            'access_description' => $this->access_description,
            'access_difficulty' => $this->access_difficulty,
            'has_parking' => $this->has_parking,
            'safety_notes' => $this->safety_notes,
            'fishing_types' => FishingTypeResource::collection($this->whenLoaded('fishingTypes')),
            'protected_area' => $this->whenLoaded(
                'protectedArea',
                fn () => $this->protectedArea ? [
                    'slug' => $this->protectedArea->slug,
                    'name' => $this->protectedArea->name,
                    'restriction_level' => $this->protectedArea->restriction_level->value,
                    'restriction_level_label' => $this->protectedArea->restriction_level->label(),
                    'restrictions' => $this->protectedArea->restrictions,
                ] : null,
            ),
            'published_at' => $this->published_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
