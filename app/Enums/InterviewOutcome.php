<?php

namespace App\Enums;

enum InterviewOutcome: string
{
    case PENDING = 'pending';
    case POSITIVE = 'positive';
    case NEUTRAL = 'neutral';
    case NEGATIVE = 'negative';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::POSITIVE => 'Positive',
            self::NEUTRAL => 'Neutral',
            self::NEGATIVE => 'Negative',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::POSITIVE => 'green',
            self::NEUTRAL => 'yellow',
            self::NEGATIVE => 'red',
        };
    }
}
