<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\BulkApprovePendingActionsRequest;
use App\Http\Requests\RejectPendingActionRequest;
use App\Models\PendingAction;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Throwable;

class PendingActionsController extends Controller
{
    public function __construct(protected PendingActionApplier $applier) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', PendingAction::class);

        $query = PendingAction::query()
            ->with(['user:id,name,email', 'agentToken:id,name,agent_slug', 'reviewer:id,name'])
            ->orderByDesc('created_at');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending + recently applied/rejected so users can undo within window.
            $query->whereIn('status', [
                PendingAction::STATUS_PENDING,
                PendingAction::STATUS_APPLIED,
                PendingAction::STATUS_REJECTED,
                PendingAction::STATUS_FAILED,
            ]);
        }

        if ($agent = $request->input('agent')) {
            $query->where('agent_slug', $agent);
        }

        if ($module = $request->input('module')) {
            $query->where('tool', 'LIKE', $module.'.%');
        }

        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to);
        }

        $perPage = (int) min(max((int) $request->input('per_page', 25), 1), 100);

        return Inertia::render('PendingActions/Index', [
            'pendingActions' => $query->paginate($perPage)->withQueryString(),
            'filters' => $request->only(['status', 'agent', 'module', 'from', 'to']),
            'pendingCount' => PendingAction::query()->where('status', PendingAction::STATUS_PENDING)->count(),
        ]);
    }

    public function show(PendingAction $pendingAction)
    {
        Gate::authorize('view', $pendingAction);

        $pendingAction->load([
            'user:id,name,email',
            'agentToken:id,name,agent_slug',
            'reviewer:id,name',
            'reverter:id,name',
        ]);

        return Inertia::render('PendingActions/Show', [
            'pendingAction' => $pendingAction,
            'canApprove' => Gate::check('approve', $pendingAction),
            'canReject' => Gate::check('reject', $pendingAction),
            'canRevert' => Gate::check('revert', $pendingAction),
        ]);
    }

    public function approve(PendingAction $pendingAction)
    {
        Gate::authorize('approve', $pendingAction);

        try {
            $this->applier->apply($pendingAction, request()->user());
        } catch (Throwable $e) {
            return back()->with('error', 'Could not apply: '.$e->getMessage());
        }

        return back()->with('success', 'Pending action applied.');
    }

    public function reject(RejectPendingActionRequest $request, PendingAction $pendingAction)
    {
        Gate::authorize('reject', $pendingAction);

        $this->applier->reject($pendingAction, $request->user(), $request->input('reason'));

        return back()->with('success', 'Pending action rejected.');
    }

    public function revert(PendingAction $pendingAction)
    {
        Gate::authorize('revert', $pendingAction);

        try {
            $this->applier->revert($pendingAction, request()->user());
        } catch (Throwable $e) {
            return back()->with('error', 'Could not revert: '.$e->getMessage());
        }

        return back()->with('success', 'Action reverted.');
    }

    public function bulkApprove(BulkApprovePendingActionsRequest $request)
    {
        $applied = 0;
        $failed = [];

        foreach ($request->input('ids', []) as $id) {
            $action = PendingAction::find($id);

            if ($action === null || ! Gate::check('approve', $action)) {
                continue;
            }

            try {
                $this->applier->apply($action, $request->user());
                $applied++;
            } catch (Throwable $e) {
                $failed[] = ['id' => $action->id, 'reason' => $e->getMessage()];
            }
        }

        return back()->with('success', "Applied {$applied} action(s).".(
            $failed === [] ? '' : ' '.count($failed).' failed.'
        ))->with('bulkFailures', $failed);
    }
}
