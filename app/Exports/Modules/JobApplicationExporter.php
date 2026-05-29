<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Model;

class JobApplicationExporter extends AbstractModuleExporter
{
    protected string $model = JobApplication::class;

    protected ?string $dateColumn = 'applied_at';

    public function key(): string
    {
        return 'job-applications';
    }

    public function label(): string
    {
        return 'Job Applications';
    }

    public function headers(): array
    {
        return [
            'Company', 'Job Title', 'Location', 'Remote', 'Salary Min', 'Salary Max', 'Currency',
            'Status', 'Source', 'Applied At', 'Next Action At', 'Priority', 'Contact Name',
            'Contact Email', 'Tags', 'Notes', 'Created At',
        ];
    }

    /**
     * @param  JobApplication  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->company_name,
            $model->job_title,
            $model->location,
            $this->bool($model->remote),
            $model->salary_min,
            $model->salary_max,
            $model->currency,
            $this->enumLabel($model->status),
            $this->enumLabel($model->source),
            $this->datetime($model->applied_at),
            $this->datetime($model->next_action_at),
            $model->priority,
            $model->contact_name,
            $model->contact_email,
            $this->list($model->tags),
            $model->notes,
            $this->datetime($model->created_at),
        ];
    }
}
