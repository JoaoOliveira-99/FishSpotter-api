<?php

namespace App\Models;

use App\Enums\Coast;
use Database\Factories\ForecastPointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastPoint extends Model
{
    /** @use HasFactory<ForecastPointFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'coast',
        'warning_area_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'coast' => Coast::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<ForecastPoint>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
