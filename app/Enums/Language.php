<?php

namespace App\Enums;

enum Language: string
{
    case AR = 'ar';
    case EN = 'en';

    public static function values(): array
    {
        return array_column(self::cases(), 'value'); // ['ar','en']
    }

    public static function labels(): array
    {
        return [
            self::AR->value => 'Arabic',
            self::EN->value => 'English',
        ];
    }
}
