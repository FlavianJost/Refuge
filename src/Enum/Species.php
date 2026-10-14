<?php

namespace App\Enum;

enum Species: string
{
    case DOG = 'dog';
    case CAT = 'cat';
    case RABBIT = 'rabbit';
    case BIRD = 'bird';
    case RODENT = 'rodent';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DOG => 'Chien',
            self::CAT => 'Chat',
            self::RABBIT => 'Lapin',
            self::BIRD => 'Oiseau',
            self::RODENT => 'Rongeur',
            self::OTHER => 'Autre',
        };
    }
}
