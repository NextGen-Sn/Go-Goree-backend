<?php

declare(strict_types=1);

namespace App\Enums;

enum StatutBilletEnum: string
{
    case EN_ATTENTE_PAIEMENT = 'EN_ATTENTE_PAIEMENT';
    case PAYE = 'PAYE';
    case UTILISE = 'UTILISE';
    case EXPIRE = 'EXPIRE';
    case ANNULE = 'ANNULE';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE_PAIEMENT => 'En attente paiement',
            self::PAYE => 'Payé',
            self::UTILISE => 'Utilisé',
            self::EXPIRE => 'Expiré',
            self::ANNULE => 'Annulé',
        };
    }
}
