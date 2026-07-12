<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationEnum: string
{
    case PAYEMENT = 'PAYEMENT';
    case ALERTE = 'ALERTE';

    public function label(): string
    {
        return match ($this) {
            self::PAYEMENT => 'Paiement',
            self::ALERTE => 'Alerte',
        };
    }
}
