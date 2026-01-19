<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case PAID = 'paid';
    case PARTIALLY_PAID = 'partially_paid';
    case PAST_DUE = 'past_due';
    case VOID = 'void';
    case WRITTEN_OFF = 'written_off';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ISSUED => 'Issued',
            self::PAID => 'Paid',
            self::PARTIALLY_PAID => 'Partially Paid',
            self::PAST_DUE => 'Past Due',
            self::VOID => 'Void',
            self::WRITTEN_OFF => 'Written Off',
            self::ARCHIVED => 'Archived',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::ISSUED => 'blue',
            self::PAID => 'green',
            self::PARTIALLY_PAID => 'yellow',
            self::PAST_DUE => 'red',
            self::VOID => 'gray',
            self::WRITTEN_OFF => 'red',
            self::ARCHIVED => 'slate',
        };
    }
}
