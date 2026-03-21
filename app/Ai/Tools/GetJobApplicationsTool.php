<?php

namespace App\Ai\Tools;

use App\Models\JobApplication;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetJobApplicationsTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get the job application pipeline — active applications, status breakdown, and upcoming interviews.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): Stringable|string
    {
        $userId = Auth::id();

        $all = JobApplication::where('user_id', $userId)->get();
        $active = $all->whereNotIn('status', ['rejected', 'withdrawn']);

        return json_encode([
            'total_applications' => $all->count(),
            'active_applications' => $active->count(),
            'by_status' => $all->groupBy('status')->map->count(),
            'pipeline' => $active->sortByDesc('created_at')->values()->map(fn ($a) => [
                'company' => $a->company_name,
                'role' => $a->job_title,
                'status' => $a->status,
                'applied_date' => $a->applied_at?->format('Y-m-d'),
                'expected_salary' => $a->expected_salary,
                'currency' => $a->currency,
                'priority' => $a->priority,
            ])->values(),
        ]);
    }
}
