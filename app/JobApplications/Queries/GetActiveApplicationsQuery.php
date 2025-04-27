<?php

namespace App\JobApplications\Queries;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GetActiveApplicationsQuery
{
    public function execute(array $params): Collection
    {
        $activeStatuses = ['applied', 'interviewing'];

        return DB::table('job_applications')
            ->where('user_id', $params['user_id'])
            ->whereIn('status', $activeStatuses)
            ->orderBy('application_date', 'desc')
            ->get();
    }
}
