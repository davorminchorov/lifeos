<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Expenses\BulkImportExpenses;
use App\Mcp\Tools\Expenses\CategorizeExpense;
use App\Mcp\Tools\Expenses\CreateExpense;
use App\Models\AgentToken;
use App\Models\Expense;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class WriteToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();

        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);
    }

    private function expenseArgs(array $overrides = []): array
    {
        return array_merge([
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
            'category' => 'groceries',
            'description' => 'Weekly groceries',
        ], $overrides);
    }

    public function test_expenses_create_returns_pending_action(): void
    {
        LifeOsServer::tool(CreateExpense::class, $this->expenseArgs())
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertArrayHasKey('pending_action_id', $content);
                $this->assertSame(PendingAction::STATUS_PENDING, $content['status']);
                $this->assertNotEmpty($content['idempotency_key']);
                $this->assertFalse($content['auto_applied']);

                return true;
            });

        $this->assertSame(1, PendingAction::query()->count());
        $this->assertSame(0, Expense::query()->count(), 'No expense before approval.');
    }

    public function test_expenses_create_is_idempotent(): void
    {
        LifeOsServer::tool(CreateExpense::class, $this->expenseArgs());
        LifeOsServer::tool(CreateExpense::class, $this->expenseArgs());

        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_expenses_create_is_unauthorized_without_matching_ability(): void
    {
        [$restricted] = AgentToken::issue($this->user, $this->tenant, 'restricted', ['expenses.list']);
        App::instance('agent.token', $restricted);

        LifeOsServer::tool(CreateExpense::class, $this->expenseArgs())
            ->assertHasErrors(['Agent token is not authorized to call [expenses.create].']);

        $this->assertSame(0, PendingAction::query()->count());
    }

    public function test_expenses_bulk_import_creates_one_pending_row(): void
    {
        LifeOsServer::tool(BulkImportExpenses::class, [
            'items' => [
                $this->expenseArgs(),
                $this->expenseArgs(['amount' => 5.0, 'merchant' => 'Konzum']),
            ],
        ])
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertSame(2, $content['item_count']);
                $this->assertSame(PendingAction::STATUS_PENDING, $content['status']);

                return true;
            });

        $this->assertSame(1, PendingAction::query()->count());
        $this->assertSame('expenses.bulkImport', PendingAction::query()->first()->tool);
    }

    public function test_expenses_categorize_requires_existing_expense_in_tenant(): void
    {
        // Foreign expense in another tenant.
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();
        $this->actingAs($other);
        $foreign = Expense::factory()->create();

        // Restore primary acting user.
        $this->actingAs($this->user);

        LifeOsServer::tool(CategorizeExpense::class, [
            'expense_id' => $foreign->id,
            'category' => 'travel',
        ])->assertHasErrors([
            "Expense [{$foreign->id}] not found in this tenant.",
        ]);
    }

    public function test_expenses_categorize_records_pending_action_for_local_expense(): void
    {
        $expense = Expense::factory()->create();

        LifeOsServer::tool(CategorizeExpense::class, [
            'expense_id' => $expense->id,
            'category' => 'travel',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->count());
        $action = PendingAction::query()->first();
        $this->assertSame('expenses.categorize', $action->tool);
        $this->assertSame($expense->id, (int) $action->payload['expense_id']);
        $this->assertSame('travel', $action->payload['category']);
    }
}
