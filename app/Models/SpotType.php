<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    // Quando criares fishing_spots:
    // public function fishingSpots(): HasMany
    // {
    //     return $this->hasMany(FishingSpot::class);
    // }
}
