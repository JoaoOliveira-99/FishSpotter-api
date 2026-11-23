<?php

namespace App\Models;

use App\Enums\Island;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'island',
    ];

    protected function casts(): array
    {
        return [
            'island' => Island::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function fishingSpots(): HasMany
    {
        return $this->hasMany(FishingSpot::class);
    }
}
