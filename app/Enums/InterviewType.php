<?php

namespace App\Enums;

enum InterviewType: string
{
    case PHONE = 'phone';
    case VIDEO = 'video';
    case ONSITE = 'onsite';
    case PANEL = 'panel';
    case TECHNICAL = 'technical';
    case BEHAVIORAL = 'behavioral';
    case FINAL = 'final';

    public function label(): string
    {
        return match ($this) {
            self::PHONE => 'Phone',
            self::VIDEO => 'Video',
            self::ONSITE => 'On-site',
            self::PANEL => 'Panel',
            self::TECHNICAL => 'Technical',
            self::BEHAVIORAL => 'Behavioral',
            self::FINAL => 'Final',
        };
    }
}
