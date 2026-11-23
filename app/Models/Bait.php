<?php

namespace App\Models;

use App\Enums\BaitKind;
use Illuminate\Database\Eloquent\Model;

class Bait extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'kind',
    ];

    protected function casts(): array
    {
        return [
            'kind' => BaitKind::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
