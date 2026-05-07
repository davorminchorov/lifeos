<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Bills\CreateUtilityBill;
use App\Mcp\Tools\Contracts\CreateContract;
use App\Mcp\Tools\Iou\CreateIou;
use App\Mcp\Tools\Jobs\AddInterview;
use App\Mcp\Tools\Jobs\UpdateJobStatus;
use App\Mcp\Tools\Subscriptions\CreateSubscription;
use App\Mcp\Tools\Warranties\CreateWarranty;
use App\Models\AgentToken;
use App\Models\Contract;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\PendingAction;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityBill;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class Phase4WriteToolsTest extends TestCase
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

    public function test_subscriptions_create_records_pending_action(): void
    {
        LifeOsServer::tool(CreateSubscription::class, [
            'service_name' => 'Netflix',
            'cost' => 9.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'start_date' => '2026-05-01',
        ])->assertOk()->assertStructuredContent(function (AssertableJson $json) {
            $json->where('status', PendingAction::STATUS_PENDING)->etc();
        });

        $this->assertSame(1, PendingAction::query()->where('tool', 'subscriptions.create')->count());
        $this->assertSame(0, Subscription::query()->count());
    }

    public function test_apply_subscriptions_create_writes_with_agent_attribution(): void
    {
        LifeOsServer::tool(CreateSubscription::class, [
            'service_name' => 'Netflix',
            'category' => 'streaming',
            'cost' => 9.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
            'start_date' => '2026-05-01',
            'next_billing_date' => '2026-06-01',
        ]);

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(1, Subscription::query()->count());
        $sub = Subscription::query()->first();
        $this->assertSame('agent', $sub->source);
        $this->assertNotNull($sub->created_by_agent_token_id);
    }

    public function test_contracts_create_records_pending_action(): void
    {
        LifeOsServer::tool(CreateContract::class, [
            'title' => 'Office Lease',
            'counterparty' => 'Landlord Co',
            'contract_type' => 'lease',
            'start_date' => '2026-05-01',
            'end_date' => '2027-05-01',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'contracts.create')->count());
        $this->assertSame(0, Contract::query()->count());
    }

    public function test_warranties_create_idempotent_by_serial(): void
    {
        $args = [
            'product_name' => 'Laptop',
            'brand' => 'Apple',
            'serial_number' => 'ABC123',
            'purchase_date' => '2026-05-01',
            'warranty_expiration_date' => '2027-05-01',
        ];

        LifeOsServer::tool(CreateWarranty::class, $args);
        LifeOsServer::tool(CreateWarranty::class, $args);

        $this->assertSame(1, PendingAction::query()->where('tool', 'warranties.create')->count());
    }

    public function test_iou_create_records_pending_action(): void
    {
        LifeOsServer::tool(CreateIou::class, [
            'type' => 'owed',
            'person_name' => 'Alex',
            'amount' => 50,
            'currency' => 'EUR',
            'transaction_date' => '2026-05-01',
            'description' => 'Dinner reimbursement',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'iou.create')->count());
        $this->assertSame(0, Iou::query()->count());
    }

    public function test_utility_bills_create_records_pending_action(): void
    {
        LifeOsServer::tool(CreateUtilityBill::class, [
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 100,
            'currency' => 'EUR',
            'due_date' => '2026-05-15',
            'bill_period_end' => '2026-04-30',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'utilityBills.create')->count());
        $this->assertSame(0, UtilityBill::query()->count());
    }

    public function test_jobs_update_status_requires_existing_application(): void
    {
        LifeOsServer::tool(UpdateJobStatus::class, [
            'job_application_id' => 9999,
            'status' => 'interviewing',
        ])->assertHasErrors(['Job application [9999] not found in this tenant.']);
    }

    public function test_jobs_update_status_records_pending_action_for_local_application(): void
    {
        $app = JobApplication::factory()->create([
            'company_name' => 'Acme',
            'job_title' => 'Engineer',
        ]);

        LifeOsServer::tool(UpdateJobStatus::class, [
            'job_application_id' => $app->id,
            'status' => 'interviewing',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'jobs.updateStatus')->count());
    }

    public function test_jobs_add_interview_records_pending_action(): void
    {
        $app = JobApplication::factory()->create();

        LifeOsServer::tool(AddInterview::class, [
            'job_application_id' => $app->id,
            'scheduled_at' => '2026-05-10T14:00:00Z',
            'interview_type' => 'video',
            'interviewer_name' => 'Jane Doe',
        ])->assertOk();

        $action = PendingAction::query()->firstOrFail();
        $this->assertSame('jobs.addInterview', $action->tool);
        $this->assertSame($app->id, (int) $action->payload['job_application_id']);
    }

    public function test_cross_tenant_categorize_target_invisible(): void
    {
        $other = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $other->id]);
        $other->forceFill(['current_tenant_id' => $otherTenant->id])->save();

        $this->actingAs($other);
        $foreignApp = JobApplication::factory()->create();

        $this->actingAs($this->user);

        LifeOsServer::tool(UpdateJobStatus::class, [
            'job_application_id' => $foreignApp->id,
            'status' => 'rejected',
        ])->assertHasErrors([
            "Job application [{$foreignApp->id}] not found in this tenant.",
        ]);
    }
}
