<?php

declare(strict_types=1);

namespace App\Enums;

// Extension du diagramme UML : workflow de revue manuelle des alertes de fraude.
enum StatutAlerteFraudeEnum: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case CONFIRMEE = 'CONFIRMEE';
    case FAUX_POSITIF = 'FAUX_POSITIF';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::CONFIRMEE => 'Confirmée',
            self::FAUX_POSITIF => 'Faux positif',
        };
    }
}
