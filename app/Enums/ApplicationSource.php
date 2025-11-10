<?php

namespace App\Enums;

enum ApplicationSource: string
{
    case LINKEDIN = 'linkedin';
    case COMPANY_WEBSITE = 'company_website';
    case JOB_BOARD = 'job_board';
    case REFERRAL = 'referral';
    case RECRUITER = 'recruiter';
    case NETWORKING = 'networking';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::LINKEDIN => 'LinkedIn',
            self::COMPANY_WEBSITE => 'Company Website',
            self::JOB_BOARD => 'Job Board',
            self::REFERRAL => 'Referral',
            self::RECRUITER => 'Recruiter',
            self::NETWORKING => 'Networking',
            self::OTHER => 'Other',
        };
    }
}
