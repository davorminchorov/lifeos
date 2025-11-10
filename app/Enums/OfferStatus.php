<?php

namespace App\Enums;

enum OfferStatus: string
{
    case PENDING = 'pending';
    case NEGOTIATING = 'negotiating';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::NEGOTIATING => 'Negotiating',
            self::ACCEPTED => 'Accepted',
            self::DECLINED => 'Declined',
            self::EXPIRED => 'Expired',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::NEGOTIATING => 'blue',
            self::ACCEPTED => 'green',
            self::DECLINED => 'red',
            self::EXPIRED => 'gray',
        };
    }
}
