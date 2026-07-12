<?php

declare(strict_types=1);

namespace App\Enums;

enum CanalEnum: string
{
    case SMS = 'SMS';
    case IN_APP = 'IN_APP';
    case MAIL = 'MAIL';

    public function label(): string
    {
        return match ($this) {
            self::SMS => 'SMS',
            self::IN_APP => 'In-App',
            self::MAIL => 'E-mail',
        };
    }
}
