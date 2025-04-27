<?php

namespace App\JobApplications\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetInterviewScheduleQuery
{
    public function execute(array $params): Collection
    {
        $query = DB::table('job_application_interviews as i')
            ->join('job_applications as a', 'i.application_id', '=', 'a.id')
            ->where('a.user_id', $params['user_id'])
            ->orderBy('i.interview_date', 'asc')
            ->orderBy('i.interview_time', 'asc')
            ->select([
                'i.id',
                'i.application_id',
                'i.interview_date',
                'i.interview_time',
                'i.interview_type',
                'i.with_person',
                'i.location',
                'i.notes',
                'a.company_name',
                'a.position'
            ]);

        if (isset($params['start_date'])) {
            $query->where('i.interview_date', '>=', $params['start_date']);
        }

        if (isset($params['end_date'])) {
            $query->where('i.interview_date', '<=', $params['end_date']);
        }

        return $query->get();
    }
}
