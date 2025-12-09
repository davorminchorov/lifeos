<?php

namespace App\Policies;

use App\Models\CycleMenuDay;
use App\Models\User;

class CycleMenuDayPolicy
{
    public function update(?User $user, CycleMenuDay $day): bool
    {
        return (bool) $user;
    }
}
