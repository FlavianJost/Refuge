<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DonationStatus: string implements TranslatableInterface
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PAID => 'Confirmé',
            self::PENDING => 'En attente',
            self::FAILED => 'Échoué',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PAID => 'success',
            self::PENDING => 'warning',
            self::FAILED => 'danger',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
