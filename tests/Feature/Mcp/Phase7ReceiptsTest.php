<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Expenses\CreateExpense;
use App\Mcp\Tools\Receipts\ProcessedFiles;
use App\Models\AgentToken;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class Phase7ReceiptsTest extends TestCase
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

    public function test_expense_create_round_trip_with_file_id_is_idempotent(): void
    {
        $args = [
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
            'category' => 'groceries',
            'description' => 'Groceries',
            'source_file_id' => 'drive-receipt-001',
        ];

        LifeOsServer::tool(CreateExpense::class, $args);
        LifeOsServer::tool(CreateExpense::class, $args);

        $this->assertSame(1, PendingAction::query()->where('tool', 'expenses.create')->count());
        $this->assertSame('drive-receipt-001', PendingAction::query()->first()->payload['source_file_id']);
    }

    public function test_expense_create_with_different_file_ids_creates_distinct_pending_actions(): void
    {
        $base = [
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
            'category' => 'groceries',
            'description' => 'Groceries',
        ];

        LifeOsServer::tool(CreateExpense::class, [...$base, 'source_file_id' => 'drive-001']);
        LifeOsServer::tool(CreateExpense::class, [...$base, 'source_file_id' => 'drive-002']);

        $this->assertSame(2, PendingAction::query()->where('tool', 'expenses.create')->count());
    }

    public function test_processed_files_lists_seen_file_ids(): void
    {
        $base = [
            'amount' => 12.50,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Lidl',
            'category' => 'groceries',
            'description' => 'Groceries',
        ];

        LifeOsServer::tool(CreateExpense::class, [...$base, 'source_file_id' => 'drive-001']);
        LifeOsServer::tool(CreateExpense::class, [...$base, 'merchant' => 'Konzum', 'source_file_id' => 'drive-002']);
        // A submission without a file_id should be ignored by the listing.
        LifeOsServer::tool(CreateExpense::class, [...$base, 'merchant' => 'Tinex']);

        LifeOsServer::tool(ProcessedFiles::class)
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $ids = collect($content['items'])->pluck('source_file_id')->all();
                $this->assertSame(2, $content['count']);
                $this->assertContains('drive-001', $ids);
                $this->assertContains('drive-002', $ids);

                return true;
            });
    }

    public function test_processed_files_does_not_leak_other_tenants(): void
    {
        // Foreign tenant + foreign pending action with a Drive file id.
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();
        [$otherToken] = AgentToken::issue($other, $otherTenant, 'foreign', ['*']);

        $this->actingAs($other);
        App::instance('agent.token', $otherToken);
        LifeOsServer::tool(CreateExpense::class, [
            'amount' => 99,
            'currency' => 'EUR',
            'expense_date' => '2026-05-01',
            'merchant' => 'Foreign',
            'category' => 'x',
            'description' => 'x',
            'source_file_id' => 'foreign-drive-id',
        ]);

        // Switch back to primary user.
        $this->actingAs($this->user);
        [$token] = AgentToken::issue($this->user, $this->tenant, 'primary', ['*']);
        App::instance('agent.token', $token);

        LifeOsServer::tool(ProcessedFiles::class)
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertSame(0, $content['count']);

                return true;
            });
    }

    public function test_processed_files_picks_up_bulk_import_items(): void
    {
        // Simulate a bulk import payload that carries source_file_id per item.
        PendingAction::query()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'tool' => 'expenses.bulkImport',
            'action' => PendingAction::ACTION_BULK_CREATE,
            'idempotency_key' => hash('sha256', 'phase7-bulk'),
            'status' => PendingAction::STATUS_PENDING,
            'payload' => [
                'items' => [
                    [
                        'amount' => 5,
                        'currency' => 'EUR',
                        'expense_date' => '2026-05-01',
                        'merchant' => 'Lidl',
                        'category' => 'groceries',
                        'description' => 'Bagel',
                        'source_file_id' => 'drive-bulk-001',
                    ],
                ],
            ],
        ]);

        LifeOsServer::tool(ProcessedFiles::class)
            ->assertOk()
            ->assertStructuredContent(function (array $content): bool {
                $this->assertSame(1, $content['count']);
                $this->assertSame('drive-bulk-001', $content['items'][0]['source_file_id']);

                return true;
            });
    }
}
