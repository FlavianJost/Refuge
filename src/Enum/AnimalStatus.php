<?php

namespace App\Enum;

enum AnimalStatus: string
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
}
