<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum VisitAppointmentStatus: string implements TranslatableInterface
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REFUSED = 'refused';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::CONFIRMED => 'Confirmé',
            self::REFUSED => 'Refusé',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::CONFIRMED => 'success',
            self::REFUSED => 'danger',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
