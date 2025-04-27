<?php

namespace App\JobApplications\Queries;

use App\JobApplications\Domain\JobApplication;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetJobApplicationsQuery
{
    public function execute(array $params): Collection
    {
        $query = DB::table('job_applications')
            ->where('user_id', $params['user_id'])
            ->orderBy('created_at', 'desc');

        // Apply filters if provided
        if (isset($params['filters'])) {
            if (isset($params['filters']['status'])) {
                $query->where('status', $params['filters']['status']);
            }

            if (isset($params['filters']['company_name'])) {
                $query->where('company_name', 'like', '%' . $params['filters']['company_name'] . '%');
            }

            if (isset($params['filters']['position'])) {
                $query->where('position', 'like', '%' . $params['filters']['position'] . '%');
            }

            if (isset($params['filters']['from_date'])) {
                $query->where('application_date', '>=', $params['filters']['from_date']);
            }

            if (isset($params['filters']['to_date'])) {
                $query->where('application_date', '<=', $params['filters']['to_date']);
            }
        }

        return $query->get()
            ->map(function ($application) {
                return $this->hydrateJobApplication($application);
            });
    }

    private function hydrateJobApplication($data): JobApplication
    {
        // Get interviews for this application
        $interviews = DB::table('job_application_interviews')
            ->where('application_id', $data->id)
            ->orderBy('interview_date', 'asc')
            ->orderBy('interview_time', 'asc')
            ->get()
            ->toArray();

        // Get outcome information if exists
        $outcome = DB::table('job_application_outcomes')
            ->where('application_id', $data->id)
            ->first();

        return new JobApplication(
            $data->id,
            $data->user_id,
            $data->company_name,
            $data->position,
            $data->application_date,
            $data->job_description,
            $data->application_url,
            $data->salary_range,
            $data->contact_person,
            $data->contact_email,
            $data->status,
            $data->notes,
            $interviews ? json_decode(json_encode($interviews), true) : [],
            $outcome ? json_decode(json_encode($outcome), true) : null,
            $data->created_at,
            $data->updated_at
        );
    }
}
