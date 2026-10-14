<?php

namespace App\Enum;

enum AdoptionRequestStatus: string
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
}
