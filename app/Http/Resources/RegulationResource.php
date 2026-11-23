<?php

namespace App\Http\Resources;

use App\Models\Regulation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Usado dentro de outros recursos (licenças, espécies, áreas protegidas),
 * para a app mostrar a legislação de origem e a data de verificação.
 *
 * @mixin Regulation
 */
class RegulationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'reference' => $this->reference,
            'title' => $this->title,
            'summary' => $this->summary,
            'url' => $this->url,
            'published_on' => $this->published_on?->toDateString(),
            'verified_at' => $this->verified_at->toIso8601ZuluString(),
        ];
    }
}
