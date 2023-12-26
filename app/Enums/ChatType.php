<?php

namespace App\Enums;

enum ChatType: string
{
    case PROMPT = 'prompt';
    case RESPONSE = 'response';

    public static function values(): array
    {
        return [
            self::PROMPT,
            self::RESPONSE,
        ];
    }
}
