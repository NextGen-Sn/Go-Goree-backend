<?php

declare(strict_types=1);

namespace App\Enums;

enum StatutPayementEnum: string
{
    case EN_COURS = 'EN_COURS';
    case ACCEPTE = 'ACCEPTE';
    case REFUSE = 'REFUSE';
    case SUSPECT = 'SUSPECT';

    public function label(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours',
            self::ACCEPTE => 'Accepté',
            self::REFUSE => 'Refusé',
            self::SUSPECT => 'Suspect',
        };
    }
}
