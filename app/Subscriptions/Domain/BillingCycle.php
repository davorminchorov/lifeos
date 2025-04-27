<?php

namespace App\Subscriptions\Domain;

use DateTimeImmutable;

enum BillingCycle: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case BIWEEKLY = 'biweekly';
    case MONTHLY = 'monthly';
    case BIMONTHLY = 'bimonthly';
    case QUARTERLY = 'quarterly';
    case SEMIANNUALLY = 'semiannually';
    case ANNUALLY = 'annually';

    public function label(): string
    {
        return match($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::BIWEEKLY => 'Biweekly',
            self::MONTHLY => 'Monthly',
            self::BIMONTHLY => 'Bimonthly',
            self::QUARTERLY => 'Quarterly',
            self::SEMIANNUALLY => 'Semiannually',
            self::ANNUALLY => 'Annually',
        };
    }

    public function calculateNextDate(DateTimeImmutable $fromDate): DateTimeImmutable
    {
        return match($this) {
            self::DAILY => $fromDate->modify('+1 day'),
            self::WEEKLY => $fromDate->modify('+1 week'),
            self::BIWEEKLY => $fromDate->modify('+2 weeks'),
            self::MONTHLY => $fromDate->modify('+1 month'),
            self::BIMONTHLY => $fromDate->modify('+2 months'),
            self::QUARTERLY => $fromDate->modify('+3 months'),
            self::SEMIANNUALLY => $fromDate->modify('+6 months'),
            self::ANNUALLY => $fromDate->modify('+1 year'),
        };
    }
}
