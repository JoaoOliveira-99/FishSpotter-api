<?php

namespace App\Http\Resources;

use App\Models\LicenseType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin LicenseType
 */
class LicenseTypeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'modality' => $this->modality->value,
            'modality_label' => $this->modality->label(),
            'summary' => $this->summary,
            'requirements' => $this->requirements ?? [],
            'validity_info' => $this->validity_info,
            'fee_info' => $this->fee_info,
            'official_url' => $this->official_url,
            'verified_at' => $this->verified_at->toIso8601ZuluString(),
            'regulation' => $this->whenLoaded(
                'regulation',
                fn () => $this->regulation ? new RegulationResource($this->regulation) : null,
            ),
        ];
    }
}
