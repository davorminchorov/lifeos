<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PendingAction;
use App\Models\User;

class PendingActionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->current_tenant_id !== null;
    }

    public function view(User $user, PendingAction $action): bool
    {
        return $user->current_tenant_id === $action->tenant_id;
    }

    public function approve(User $user, PendingAction $action): bool
    {
        return $this->view($user, $action) && $action->isPending();
    }

    public function reject(User $user, PendingAction $action): bool
    {
        return $this->view($user, $action) && $action->isPending();
    }

    public function revert(User $user, PendingAction $action): bool
    {
        return $this->view($user, $action) && $action->isRevertable();
    }
}
