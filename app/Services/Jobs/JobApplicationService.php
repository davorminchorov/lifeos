<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use App\Models\JobApplication;
use App\Models\JobApplicationInterview;
use App\Models\User;

class JobApplicationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, array $data): JobApplication
    {
        return JobApplication::create([
            'user_id' => $user->id,
            ...$data,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(JobApplication $application, array $data): JobApplication
    {
        $application->update($data);

        return $application->refresh();
    }

    public function delete(JobApplication $application): bool
    {
        return (bool) $application->delete();
    }

    public function archive(JobApplication $application): JobApplication
    {
        return $this->update($application, ['archived_at' => now()]);
    }

    public function unarchive(JobApplication $application): JobApplication
    {
        return $this->update($application, ['archived_at' => null]);
    }

    /**
     * Update the application's pipeline status (and optionally a follow-up date).
     * Status values are validated upstream by the FormRequest / MCP tool.
     */
    public function updateStatus(JobApplication $application, string $status, ?\DateTimeInterface $nextActionAt = null): JobApplication
    {
        $payload = ['status' => $status];

        if ($nextActionAt !== null) {
            $payload['next_action_at'] = $nextActionAt;
        }

        return $this->update($application, $payload);
    }

    /**
     * Add a scheduled interview to an application.
     *
     * @param  array<string, mixed>  $data
     */
    public function addInterview(JobApplication $application, array $data): JobApplicationInterview
    {
        return $application->interviews()->create($data);
    }
}
