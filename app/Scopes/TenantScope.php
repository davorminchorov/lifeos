<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check() || ! auth()->user()->current_tenant_id) {
            // Fail-closed: prevent access to any tenant data when no tenant is set
            $builder->whereRaw('1 = 0');
            return;
        }

        $builder->where($model->getTable().'.tenant_id', auth()->user()->current_tenant_id);
    }
}
