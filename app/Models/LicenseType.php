<?php

namespace App\Models;

use App\Enums\LicenseModality;
use Database\Factories\LicenseTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LicenseType extends Model
{
    /** @use HasFactory<LicenseTypeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'name',
        'modality',
        'summary',
        'requirements',
        'validity_info',
        'fee_info',
        'official_url',
        'regulation_id',
        'sort_order',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'modality' => LicenseModality::class,
            'requirements' => 'array',
            'sort_order' => 'integer',
            'verified_at' => 'immutable_datetime',
        ];
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
