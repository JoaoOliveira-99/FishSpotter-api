<?php

namespace App\Enums;

enum LicenseModality: string
{
    case Apeada = 'apeada';
    case Embarcada = 'embarcada';
    case Submarina = 'submarina';

    public function label(): string
    {
        return match ($this) {
            self::Apeada => 'Pesca apeada',
            self::Embarcada => 'Pesca embarcada',
            self::Submarina => 'Pesca submarina',
        };
    }
}
