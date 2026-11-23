<?php

namespace App\Http\Resources;

use App\Models\FishingType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FishingType
 */
class FishingTypeResource extends JsonResource
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
            'license_modality' => $this->license_modality?->value,
            'license_modality_label' => $this->license_modality?->label(),
        ];
    }
}
