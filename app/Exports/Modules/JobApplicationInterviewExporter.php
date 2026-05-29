<?php

declare(strict_types=1);

namespace App\Exports\Modules;

use App\Exports\AbstractModuleExporter;
use App\Models\JobApplicationInterview;
use Illuminate\Database\Eloquent\Model;

class JobApplicationInterviewExporter extends AbstractModuleExporter
{
    protected string $model = JobApplicationInterview::class;

    protected ?string $dateColumn = 'scheduled_at';

    protected array $with = ['jobApplication'];

    public function key(): string
    {
        return 'job-application-interviews';
    }

    public function label(): string
    {
        return 'Job Application Interviews';
    }

    public function headers(): array
    {
        return [
            'Company', 'Job Title', 'Type', 'Scheduled At', 'Duration (min)', 'Location',
            'Interviewer', 'Outcome', 'Completed', 'Notes',
        ];
    }

    /**
     * @param  JobApplicationInterview  $model
     */
    public function mapRow(Model $model): array
    {
        return [
            $model->jobApplication?->company_name,
            $model->jobApplication?->job_title,
            $this->enumLabel($model->type),
            $this->datetime($model->scheduled_at),
            $model->duration_minutes,
            $model->location,
            $model->interviewer_name,
            $this->enumLabel($model->outcome),
            $this->bool($model->completed),
            $model->notes,
        ];
    }
}
