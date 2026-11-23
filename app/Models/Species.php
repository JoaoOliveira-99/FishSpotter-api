<?php

namespace App\Models;

use App\Enums\ContentStatus;
use Database\Factories\SpeciesFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;

class Species extends Model
{
    /** @use HasFactory<SpeciesFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'species';

    protected $fillable = [
        'slug',
        'common_name',
        'scientific_name',
        'family',
        'worms_aphia_id',
        'description',
        'habitat',
        'depth_min_m',
        'depth_max_m',
        'max_size_cm',
        'min_legal_size_cm',
        'min_legal_size_regulation_id',
        'capture_prohibited',
        'safety_notes',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'worms_aphia_id' => 'integer',
            'depth_min_m' => 'integer',
            'depth_max_m' => 'integer',
            'max_size_cm' => 'integer',
            'min_legal_size_cm' => 'float',
            'capture_prohibited' => 'boolean',
            'status' => ContentStatus::class,
            'verified_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(fn (Species $species) => $species->assertValid());
    }

    public function assertValid(): void
    {
        if ($this->depth_min_m !== null && $this->depth_max_m !== null && $this->depth_min_m > $this->depth_max_m) {
            throw new InvalidArgumentException('depth_min_m não pode ser maior do que depth_max_m.');
        }

        // Informação legal tem sempre de apontar para a legislação de origem
        if ($this->min_legal_size_cm !== null && $this->min_legal_size_regulation_id === null) {
            throw new InvalidArgumentException('Um tamanho mínimo legal precisa da legislação de origem (min_legal_size_regulation_id).');
        }

        $hasLegalInfo = $this->min_legal_size_cm !== null || $this->capture_prohibited;

        if ($this->status === ContentStatus::Published && $hasLegalInfo && $this->verified_at === null) {
            throw new InvalidArgumentException('Uma espécie com informação legal só pode ser publicada depois de verificada (verified_at).');
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
     * @param  Builder<Species>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published);
    }

    /**
     * @return HasMany<SpeciesName, $this>
     */
    public function names(): HasMany
    {
        return $this->hasMany(SpeciesName::class);
    }

    /**
     * @return HasMany<SpeciesMonth, $this>
     */
    public function months(): HasMany
    {
        return $this->hasMany(SpeciesMonth::class)->orderBy('month');
    }

    /**
     * @return BelongsTo<Regulation, $this>
     */
    public function minLegalSizeRegulation(): BelongsTo
    {
        return $this->belongsTo(Regulation::class, 'min_legal_size_regulation_id');
    }

    /**
     * @return BelongsToMany<Bait, $this>
     */
    public function baits(): BelongsToMany
    {
        return $this->belongsToMany(Bait::class, 'species_baits')->withPivot('notes');
    }

    /**
     * @return BelongsToMany<FishingType, $this>
     */
    public function fishingTypes(): BelongsToMany
    {
        return $this->belongsToMany(FishingType::class, 'species_fishing_types')->withPivot('notes');
    }

    /**
     * @return BelongsToMany<FishingSpot, $this>
     */
    public function spots(): BelongsToMany
    {
        return $this->belongsToMany(FishingSpot::class, 'spot_species', 'species_id', 'spot_id')->withPivot('frequency');
    }
}
