<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AgentRun;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgentRunsController extends Controller
{
    public function index(Request $request)
    {
        $query = AgentRun::query()
            ->with(['user:id,name,email', 'agentToken:id,name,agent_slug'])
            ->orderByDesc('started_at');

        if ($slug = $request->input('agent')) {
            $query->where('agent_slug', $slug);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->input('from')) {
            $query->where('started_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('started_at', '<=', $to);
        }

        $perPage = (int) min(max((int) $request->input('per_page', 25), 1), 100);

        return Inertia::render('Agents/Index', [
            'agentRuns' => $query->paginate($perPage)->withQueryString(),
            'filters' => $request->only(['agent', 'status', 'from', 'to']),
            'agents' => AgentRun::query()
                ->select('agent_slug')
                ->distinct()
                ->pluck('agent_slug'),
        ]);
    }

    public function show(AgentRun $agentRun)
    {
        abort_unless($agentRun->tenant_id === request()->user()?->current_tenant_id, 403);

        $agentRun->load([
            'user:id,name,email',
            'agentToken:id,name,agent_slug',
            'events',
        ]);

        return Inertia::render('Agents/Show', [
            'agentRun' => [
                ...$agentRun->only([
                    'id', 'agent_slug', 'session_id', 'model', 'status',
                    'tools_called', 'pending_actions_created', 'tokens_in',
                    'tokens_out', 'cost_usd', 'error', 'started_at', 'ended_at',
                ]),
                'duration_seconds' => $agentRun->durationSeconds(),
                'user' => $agentRun->user?->only(['id', 'name', 'email']),
                'agent_token' => $agentRun->agentToken?->only(['id', 'name', 'agent_slug']),
                'events' => $agentRun->events->map(fn ($event) => [
                    'id' => $event->id,
                    'sequence' => $event->sequence,
                    'type' => $event->type,
                    'payload' => $event->payload,
                    'occurred_at' => $event->occurred_at?->toIso8601String(),
                ])->all(),
            ],
        ]);
    }
}
