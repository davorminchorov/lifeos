<?php

namespace App\Policies;

use App\Models\CycleMenuItem;
use App\Models\User;

class CycleMenuItemPolicy
{
    public function create(?User $user): bool
    {
        return (bool) $user;
    }

    public function update(?User $user, CycleMenuItem $item): bool
    {
        return (bool) $user;
    }

    public function delete(?User $user, CycleMenuItem $item): bool
    {
        return (bool) $user;
    }
}
