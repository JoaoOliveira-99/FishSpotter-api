<?php

namespace App\Enums;

enum Abundance: string
{
    case Baixa = 'baixa';
    case Media = 'media';
    case Alta = 'alta';

    public function label(): string
    {
        return match ($this) {
            self::Baixa => 'Pouco frequente',
            self::Media => 'Frequente',
            self::Alta => 'Época alta',
        };
    }
}
