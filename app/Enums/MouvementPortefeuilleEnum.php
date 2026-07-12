<?php

declare(strict_types=1);

namespace App\Enums;

enum MouvementPortefeuilleEnum: string
{
    case RECHARGE = 'RECHARGE';
    case DEBIT = 'DEBIT';

    public function label(): string
    {
        return match ($this) {
            self::RECHARGE => 'Recharge',
            self::DEBIT => 'Débit',
        };
    }
}
