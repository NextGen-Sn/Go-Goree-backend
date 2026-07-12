<?php

declare(strict_types=1);

namespace App\Enums;

enum JourEnum: string
{
    case LUNDI = 'LUNDI';
    case MARDI = 'MARDI';
    case MERCREDI = 'MERCREDI';
    case JEUDI = 'JEUDI';
    case VENDREDI = 'VENDREDI';
    case SAMEDI = 'SAMEDI';
    case DIMANCHE = 'DIMANCHE';

    public function label(): string
    {
        return match ($this) {
            self::LUNDI => 'Lundi',
            self::MARDI => 'Mardi',
            self::MERCREDI => 'Mercredi',
            self::JEUDI => 'Jeudi',
            self::VENDREDI => 'Vendredi',
            self::SAMEDI => 'Samedi',
            self::DIMANCHE => 'Dimanche',
        };
    }
}
