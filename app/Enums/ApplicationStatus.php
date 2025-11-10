<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case WISHLIST = 'wishlist';
    case APPLIED = 'applied';
    case SCREENING = 'screening';
    case INTERVIEW = 'interview';
    case ASSESSMENT = 'assessment';
    case OFFER = 'offer';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::WISHLIST => 'Wishlist',
            self::APPLIED => 'Applied',
            self::SCREENING => 'Screening',
            self::INTERVIEW => 'Interview',
            self::ASSESSMENT => 'Assessment',
            self::OFFER => 'Offer',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
            self::WITHDRAWN => 'Withdrawn',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WISHLIST => 'gray',
            self::APPLIED => 'blue',
            self::SCREENING => 'indigo',
            self::INTERVIEW => 'purple',
            self::ASSESSMENT => 'yellow',
            self::OFFER => 'green',
            self::ACCEPTED => 'emerald',
            self::REJECTED => 'red',
            self::WITHDRAWN => 'orange',
            self::ARCHIVED => 'slate',
        };
    }
}
