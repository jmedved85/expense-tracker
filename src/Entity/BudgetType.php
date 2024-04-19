<?php

namespace App\Entity;

enum BudgetType: int
{
    case MONTHLY = 1;
    case ANNUAL = 2;
    case QUARTERLY = 3;
    case BI_ANNUAL = 4;
    case WEEKLY = 5;

    public const NAMES = [
        'Monthly' => self::MONTHLY,
        'Annual' => self::ANNUAL,
        'Quarterly' => self::QUARTERLY,
        'Bi-Annual (six months)' => self::BI_ANNUAL,
        'Weekly' => self::WEEKLY,
    ];

    public static function getName(int $value): string
    {
        return match ($value) {
            self::MONTHLY->value => 'Monthly',
            self::ANNUAL->value => 'Annual',
            self::QUARTERLY->value => 'Quarterly',
            self::BI_ANNUAL->value => 'Bi-Annual (six months)',
            self::WEEKLY->value => 'Weekly',

            default => 'Unknown',
        };
    }
}
