<?php

namespace App\Models;

use App\Enums\SpeciesNameKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeciesName extends Model
{
    protected $fillable = [
        'species_id',
        'name',
        'kind',
    ];

    protected function casts(): array
    {
        return [
            'kind' => SpeciesNameKind::class,
        ];
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
