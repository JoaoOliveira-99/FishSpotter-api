<?php

namespace App\Models;

use App\Enums\Abundance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

/**
 * Chave primária composta (species_id, month): gerir sempre pela relação
 * $species->months(), apagando e voltando a criar, em vez de update() direto.
 */
class SpeciesMonth extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'species_id',
        'month',
        'abundance',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'integer',
            'abundance' => Abundance::class,
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (SpeciesMonth $row) {
            if ($row->month < 1 || $row->month > 12) {
                throw new InvalidArgumentException('O mês tem de estar entre 1 e 12.');
            }
        });
    }

    /**
     * @return BelongsTo<Species, $this>
     */
    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
