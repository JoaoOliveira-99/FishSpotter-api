<?php

namespace App\Enums;

enum Island: string
{
    case Madeira = 'madeira';
    case PortoSanto = 'porto_santo';

    public function label(): string
    {
        return match ($this) {
            self::Madeira => 'Madeira',
            self::PortoSanto => 'Porto Santo',
        };
    }
}
