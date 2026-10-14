<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Species: string implements TranslatableInterface
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

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
