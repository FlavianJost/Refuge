<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum HousingType: string implements TranslatableInterface
{
    case APARTMENT = 'apartment';
    case HOUSE = 'house';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::APARTMENT => 'Appartement',
            self::HOUSE => 'Maison',
            self::OTHER => 'Autre',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
