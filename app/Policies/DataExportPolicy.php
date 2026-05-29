<?php

namespace App\Policies;

use App\Models\DataExport;
use App\Models\User;

class DataExportPolicy
{
    /**
     * Determine whether the user can view the export.
     *
     * The tenant boundary is enforced by the global scope on the model; here
     * we narrow ownership down to the user who requested the export.
     */
    public function view(User $user, DataExport $dataExport): bool
    {
        return $dataExport->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the export.
     */
    public function delete(User $user, DataExport $dataExport): bool
    {
        return $dataExport->user_id === $user->id;
    }
}
