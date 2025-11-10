<?php

namespace App\Observers;

use App\Models\JobApplication;

class JobApplicationObserver
{
    /**
     * Handle the JobApplication "updating" event.
     * Track status changes automatically.
     */
    public function updating(JobApplication $application): void
    {
        // Check if status has changed
        if ($application->isDirty('status')) {
            $oldStatus = $application->getOriginal('status');
            $newStatus = $application->status;

            // Create status history record
            $application->statusHistories()->create([
                'user_id' => $application->user_id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'changed_at' => now(),
                'notes' => 'Status changed automatically',
            ]);
        }
    }

    /**
     * Handle the JobApplication "created" event.
     * Create initial status history entry.
     */
    public function created(JobApplication $application): void
    {
        // Create initial status history
        $application->statusHistories()->create([
            'user_id' => $application->user_id,
            'from_status' => null,
            'to_status' => $application->status,
            'changed_at' => now(),
            'notes' => 'Initial application created',
        ]);
    }
}
