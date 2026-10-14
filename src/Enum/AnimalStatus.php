<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AnimalStatus: string implements TranslatableInterface
{
    case AVAILABLE = 'available';
    case PENDING = 'pending';
    case ADOPTED = 'adopted';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::PENDING => 'En cours d\'adoption',
            self::ADOPTED => 'Adopté',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::PENDING => 'warning',
            self::ADOPTED => 'secondary',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
