<?php

namespace App\Policies;

use App\Models\CycleMenu;
use App\Models\User;

class CycleMenuPolicy
{
    public function viewAny(?User $user): bool
    {
        return (bool) $user;
    }

    public function view(?User $user, CycleMenu $cycleMenu): bool
    {
        return (bool) $user;
    }

    public function create(?User $user): bool
    {
        return (bool) $user;
    }

    public function update(?User $user, CycleMenu $cycleMenu): bool
    {
        return (bool) $user;
    }

    public function delete(?User $user, CycleMenu $cycleMenu): bool
    {
        return (bool) $user;
    }
}
