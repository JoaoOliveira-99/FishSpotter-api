<?php

namespace App\Http\Resources;

use App\Models\Bait;
use App\Models\FishingType;
use App\Models\Species;
use App\Models\SpeciesMonth;
use App\Models\SpeciesName;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Species
 */
class SpeciesResource extends JsonResource
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
            'worms_aphia_id' => $this->worms_aphia_id,
            'names' => $this->whenLoaded('names', fn () => $this->names->map(fn (SpeciesName $n) => [
                'name' => $n->name,
                'kind' => $n->kind->value,
            ])->values()),
            'description' => $this->description,
            'habitat' => $this->habitat,
            'depth_min_m' => $this->depth_min_m,
            'depth_max_m' => $this->depth_max_m,
            'max_size_cm' => $this->max_size_cm,
            'legal' => [
                'min_legal_size_cm' => $this->min_legal_size_cm,
                'capture_prohibited' => $this->capture_prohibited,
                'regulation' => $this->whenLoaded(
                    'minLegalSizeRegulation',
                    fn () => $this->minLegalSizeRegulation ? new RegulationResource($this->minLegalSizeRegulation) : null,
                ),
                'verified_at' => $this->verified_at?->toIso8601ZuluString(),
            ],
            'safety_notes' => $this->safety_notes,
            'season' => $this->whenLoaded('months', fn () => $this->months->map(fn (SpeciesMonth $m) => [
                'month' => $m->month,
                'abundance' => $m->abundance->value,
                'abundance_label' => $m->abundance->label(),
            ])->values()),
            'baits' => $this->whenLoaded('baits', fn () => $this->baits->map(fn (Bait $b) => [
                'slug' => $b->slug,
                'name' => $b->name,
                'kind' => $b->kind->value,
                'notes' => $b->pivot->notes,
            ])->values()),
            'fishing_types' => $this->whenLoaded('fishingTypes', fn () => $this->fishingTypes->map(fn (FishingType $t) => [
                'slug' => $t->slug,
                'name' => $t->name,
                'notes' => $t->pivot->notes,
            ])->values()),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
