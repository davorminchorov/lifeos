<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\JobApplicationOffer;
use Illuminate\Database\Eloquent\Model;

class JobApplicationOfferExporter extends AbstractModuleExporter
{
    protected string $model = JobApplicationOffer::class;

    protected ?string $dateColumn = 'decision_deadline';

    protected array $with = ['jobApplication'];

    public function key(): string
    {
        return 'job-application-offers';
    }

    public function label(): string
    {
        return 'Job Application Offers';
    }

    public function headers(): array
    {
        return [
            'Company', 'Job Title', 'Base Salary', 'Bonus', 'Equity', 'Currency', 'Benefits',
            'Start Date', 'Decision Deadline', 'Status', 'Notes',
        ];
    }

    /**
     * @param  JobApplicationOffer  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->jobApplication?->company_name,
            $model->jobApplication?->job_title,
            $model->base_salary,
            $model->bonus,
            $model->equity,
            $model->currency,
            $this->list($model->benefits),
            $this->date($model->start_date),
            $this->date($model->decision_deadline),
            $this->enumLabel($model->status),
            $model->notes,
        ];
    }
}
