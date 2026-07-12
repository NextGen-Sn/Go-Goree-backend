<?php

declare(strict_types=1);

namespace App\Enums;

enum CategorieEnum: string
{
    case ENFANT = 'ENFANT';
    case ADULTE = 'ADULTE';
    case RESIDENT = 'RESIDENT';
    case ETRANGER = 'ETRANGER';

    public function label(): string
    {
        return match ($this) {
            self::ENFANT => 'Enfant',
            self::ADULTE => 'Adulte',
            self::RESIDENT => 'Résident',
            self::ETRANGER => 'Étranger',
        };
    }
}
