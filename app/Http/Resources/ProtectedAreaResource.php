<?php

namespace App\Http\Resources;

use App\Models\ProtectedArea;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProtectedArea
 */
class ProtectedAreaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'restriction_level' => $this->restriction_level->value,
            'restriction_level_label' => $this->restriction_level->label(),
            'restrictions' => $this->restrictions,
            'boundary' => $this->boundary,
            'bbox' => [$this->min_lng, $this->min_lat, $this->max_lng, $this->max_lat],
            'verified_at' => $this->verified_at->toIso8601ZuluString(),
            'regulation' => $this->whenLoaded(
                'regulation',
                fn () => $this->regulation ? new RegulationResource($this->regulation) : null,
            ),
        ];
    }
}
