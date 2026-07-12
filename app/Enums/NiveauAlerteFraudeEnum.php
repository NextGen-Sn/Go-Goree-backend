<?php

declare(strict_types=1);

namespace App\Enums;

// Extension du diagramme UML : module anti-fraude absent du diagramme, requis par docs/api.md §5bis.5.
enum NiveauAlerteFraudeEnum: string
{
    case INFO = 'INFO';
    case SUSPECT = 'SUSPECT';
    case CRITIQUE = 'CRITIQUE';

    public function label(): string
    {
        return match ($this) {
            self::INFO => 'Information',
            self::SUSPECT => 'Suspect',
            self::CRITIQUE => 'Critique',
        };
    }
}
