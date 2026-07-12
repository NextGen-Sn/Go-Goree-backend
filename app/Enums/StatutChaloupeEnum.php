<?php

declare(strict_types=1);

namespace App\Enums;

enum StatutChaloupeEnum: string
{
    case ACTIVE = 'ACTIVE';
    case EN_MAINTENANCE = 'EN_MAINTENANCE';
    case PANNE = 'PANNE';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::EN_MAINTENANCE => 'En maintenance',
            self::PANNE => 'Panne',
        };
    }
}
