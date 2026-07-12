<?php

declare(strict_types=1);

namespace App\Enums;

// Extension du diagramme UML : traçabilité/statut des mouvements de portefeuille.
enum StatutMouvementEnum: string
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case VALIDE = 'VALIDE';
    case REJETE = 'REJETE';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::VALIDE => 'Validé',
            self::REJETE => 'Rejeté',
        };
    }
}
