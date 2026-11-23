<?php

namespace App\Models;

use App\Enums\RestrictionLevel;
use App\Services\GeoService;
use Database\Factories\ProtectedAreaFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProtectedArea extends Model
{
    /** @use HasFactory<ProtectedAreaFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'boundary',
        'restriction_level',
        'restrictions',
        'regulation_id',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'min_lat' => 'float',
            'max_lat' => 'float',
            'min_lng' => 'float',
            'max_lng' => 'float',
            'restriction_level' => RestrictionLevel::class,
            'verified_at' => 'immutable_datetime',
        ];
    }

    // Ao gravar o polígono, o retângulo envolvente é calculado logo (não depende de eventos)
    protected function boundary(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value === null ? null : json_decode($value, true),
            set: fn (array $value) => [
                'boundary' => json_encode($value),
                ...GeoService::boundingBox($value),
            ],
        );
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Regulation, $this>
     */
    public function regulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class);
    }
}
