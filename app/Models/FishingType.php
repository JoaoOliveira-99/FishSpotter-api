<?php

namespace App\Models;

use App\Enums\LicenseModality;
use Illuminate\Database\Eloquent\Model;

class FishingType extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'license_modality',
    ];

    protected function casts(): array
    {
        return [
            'license_modality' => LicenseModality::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
