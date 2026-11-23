<?php

namespace App\Enums;

enum Coast: string
{
    case Norte = 'norte';
    case Sul = 'sul';
    case PortoSanto = 'porto_santo';

    public function label(): string
    {
        return match ($this) {
            self::Norte => 'Costa norte',
            self::Sul => 'Costa sul',
            self::PortoSanto => 'Porto Santo',
        };
    }
}
