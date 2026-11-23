<?php

namespace App\Http\Resources;

use App\Models\Species;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Species
 */
class SpeciesSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'common_name' => $this->common_name,
            'scientific_name' => $this->scientific_name,
            'family' => $this->family,
            'max_size_cm' => $this->max_size_cm,
            'min_legal_size_cm' => $this->min_legal_size_cm,
            'capture_prohibited' => $this->capture_prohibited,
            // Só em /spots/{slug}/species
            'frequency' => $this->whenPivotLoaded('spot_species', fn () => $this->pivot->frequency),
        ];
    }
}
