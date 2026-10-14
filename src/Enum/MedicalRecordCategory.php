<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum MedicalRecordCategory: string implements TranslatableInterface
{
    case VACCINATION = 'vaccination';
    case TREATMENT = 'treatment';
    case CARE = 'care';
    case VET_VISIT = 'vet_visit';
    case SURGERY = 'surgery';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::VACCINATION => 'Vaccination',
            self::TREATMENT => 'Traitement',
            self::CARE => 'Soins',
            self::VET_VISIT => 'Visite vétérinaire',
            self::SURGERY => 'Chirurgie',
            self::OTHER => 'Autre',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::VACCINATION => 'success',
            self::TREATMENT => 'primary',
            self::CARE => 'info',
            self::VET_VISIT => 'warning',
            self::SURGERY => 'danger',
            self::OTHER => 'secondary',
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans($this->label(), [], null, $locale);
    }
}
