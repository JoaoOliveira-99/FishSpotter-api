<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpotType extends Model
{
    protected $fillable = [
        'slug',
        'name',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function fishingSpots(): HasMany
    {
        return $this->hasMany(FishingSpot::class);
    }
}
