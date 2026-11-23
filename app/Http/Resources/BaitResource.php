<?php

namespace App\Http\Resources;

use App\Models\Bait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Bait
 */
class BaitResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'name' => $this->name,
            'kind' => $this->kind->value,
            'kind_label' => $this->kind->label(),
        ];
    }
}
