<?php

namespace App\Enums;

enum SpeciesNameKind: string
{
    case Comum = 'comum';
    case Local = 'local';
    case En = 'en';

    public function label(): string
    {
        return match ($this) {
            self::Comum => 'Nome comum',
            self::Local => 'Nome local',
            self::En => 'Nome em inglês',
        };
    }
}
