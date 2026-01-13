<?php

namespace App\Enums;

enum TaxBehavior: string
{
    case INCLUSIVE = 'inclusive';
    case EXCLUSIVE = 'exclusive';

    public function label(): string
    {
        return match ($this) {
            self::INCLUSIVE => 'Tax Inclusive',
            self::EXCLUSIVE => 'Tax Exclusive',
        };
    }
}
