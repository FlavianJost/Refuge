<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum AdoptionRequestStatus: string implements TranslatableInterface
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Nouvelle',
            self::IN_PROGRESS => 'En cours de traitement',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Refusée',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PENDING => 'info',
            self::IN_PROGRESS => 'warning',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
