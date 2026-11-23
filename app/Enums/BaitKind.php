<?php

namespace App\Enums;

enum BaitKind: string
{
    case Natural = 'natural';
    case Artificial = 'artificial';

    public function label(): string
    {
        return match ($this) {
            self::Natural => 'Isco natural',
            self::Artificial => 'Amostra artificial',
        };
    }
}
