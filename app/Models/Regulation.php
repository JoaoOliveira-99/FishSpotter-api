<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'reference',
        'title',
        'summary',
        'url',
        'published_on',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'published_on' => 'immutable_date',
            'verified_at' => 'immutable_datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function licenseTypes(): HasMany
    {
        return $this->hasMany(LicenseType::class);
    }

    public function protectedAreas(): HasMany
    {
        return $this->hasMany(ProtectedArea::class);
    }
}
