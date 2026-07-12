<?php

declare(strict_types=1);

namespace App\Enums;

enum DemandeResidenceEnum: string
{
    case EN_COURS = 'EN_COURS';
    case ACCEPTEE = 'ACCEPTEE';
    case REFUSEE = 'REFUSEE';
    case ANNULEE = 'ANNULEE';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
            self::ANNULEE => 'Annulée',
        };
    }
}
