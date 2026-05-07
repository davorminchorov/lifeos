<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PendingAction>
 */
class PendingActionFactory extends Factory
{
    protected $model = PendingAction::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'agent_token_id' => null,
            'agent_slug' => 'phpunit',
            'session_id' => Str::uuid()->toString(),
            'tool' => 'expenses.create',
            'action' => 'create',
            'payload' => [
                'amount' => 12.5,
                'currency' => 'EUR',
                'category' => 'groceries',
                'expense_date' => now()->toDateString(),
                'description' => 'Test',
                'merchant' => 'Lidl',
            ],
            'idempotency_key' => hash('sha256', Str::random(16)),
            'status' => PendingAction::STATUS_PENDING,
        ];
    }
}
