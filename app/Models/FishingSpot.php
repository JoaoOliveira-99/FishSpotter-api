<?php

namespace App\Models;

use App\Enums\Coast;
use App\Enums\ContentStatus;
use App\Services\GeoService;
use Database\Factories\FishingSpotFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class FishingSpot extends Model
{
    /** @use HasFactory<FishingSpotFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'description',
        'spot_type_id',
        'municipality_id',
        'latitude',
        'longitude',
        'coast',
        'exposure_deg',
        'access_description',
        'access_difficulty',
        'has_parking',
        'safety_notes',
        'forecast_point_id',
        'status',
        'published_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'coast' => Coast::class,
            'exposure_deg' => 'integer',
            'access_difficulty' => 'integer',
            'has_parking' => 'boolean',
            'status' => ContentStatus::class,
            'published_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (FishingSpot $spot) => $spot->prepareForSave());
    }

    public function prepareForSave(): void
    {
        GeoService::assertCoordinate($this->latitude, $this->longitude);

        if ($this->exposure_deg !== null && ($this->exposure_deg < 0 || $this->exposure_deg > 359)) {
            throw new InvalidArgumentException('exposure_deg tem de estar entre 0 e 359.');
        }

        if ($this->access_difficulty !== null && ($this->access_difficulty < 1 || $this->access_difficulty > 5)) {
            throw new InvalidArgumentException('access_difficulty tem de estar entre 1 e 5.');
        }

        // Área protegida: calculada sempre a partir das coordenadas, nunca escolhida à mão
        if ($this->isDirty(['latitude', 'longitude']) || ! $this->exists) {
            $this->protected_area_id = GeoService::protectedAreaAt($this->latitude, $this->longitude)?->id;
        }

        // Ponto de previsão: o mais próximo, a não ser que tenha sido escolhido à mão
        if ($this->forecast_point_id === null || ($this->isDirty(['latitude', 'longitude', 'coast']) && ! $this->isDirty('forecast_point_id'))) {
            $this->forecast_point_id = GeoService::nearestForecastPoint($this->latitude, $this->longitude, $this->coast)?->id;
        }

        if ($this->status === ContentStatus::Published && $this->published_at === null) {
            $this->published_at = now();
        }
    }

    public function resolveRouteBinding($value, $field = null): ?self
    {
        return $this->published()->where($field ?? 'slug', $value)->firstOrFail();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<FishingSpot>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published);
    }

    /**
     * @return BelongsTo<SpotType, $this>
     */
    public function spotType(): BelongsTo
    {
        return $this->belongsTo(SpotType::class);
    }

    /**
     * @return BelongsTo<Municipality, $this>
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * @return BelongsTo<ForecastPoint, $this>
     */
    public function forecastPoint(): BelongsTo
    {
        return $this->belongsTo(ForecastPoint::class);
    }

    /**
     * @return BelongsTo<ProtectedArea, $this>
     */
    public function protectedArea(): BelongsTo
    {
        return $this->belongsTo(ProtectedArea::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsToMany<FishingType, $this>
     */
    public function fishingTypes(): BelongsToMany
    {
        return $this->belongsToMany(FishingType::class, 'spot_fishing_types', 'spot_id');
    }

    /**
     * @return BelongsToMany<Species, $this>
     */
    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'spot_species', 'spot_id', 'species_id')->withPivot('frequency');
    }
}
