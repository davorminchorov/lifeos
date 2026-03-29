<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;

abstract class TenantScopedTool implements Tool
{
    public function __construct(
        protected int $userId,
        protected int $tenantId,
    ) {}

    /**
     * Validate data against the given rules.
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>|string Validated data array on success, error string on failure.
     */
    protected function validate(array $data, array $rules): array|string
    {
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return 'Validation failed: '.$validator->errors()->toJson();
        }

        return $validator->validated();
    }

    /**
     * Return a query builder scoped to the current tenant.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function scopedQuery(string $modelClass): Builder
    {
        return $modelClass::query()->where('tenant_id', $this->tenantId);
    }
}
