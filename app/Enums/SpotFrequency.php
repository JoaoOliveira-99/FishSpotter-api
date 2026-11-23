<?php

namespace App\Enums;

enum SpotFrequency: string
{
    case Rara = 'rara';
    case Ocasional = 'ocasional';
    case Comum = 'comum';

    public function label(): string
    {
        return match ($this) {
            self::Rara => 'Rara',
            self::Ocasional => 'Ocasional',
            self::Comum => 'Comum',
        };
    }
}
