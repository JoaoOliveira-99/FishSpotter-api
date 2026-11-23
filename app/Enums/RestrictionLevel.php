<?php

namespace App\Enums;

enum RestrictionLevel: string
{
    case Interdita = 'interdita';
    case Condicionada = 'condicionada';

    public function label(): string
    {
        return match ($this) {
            self::Interdita => 'Pesca interdita',
            self::Condicionada => 'Pesca condicionada',
        };
    }
}
