<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Mcp\LifeOsServer;
use App\Mcp\Tools\Bills\UpcomingBills;
use App\Mcp\Tools\Contracts\ListContracts;
use App\Mcp\Tools\CycleMenu\CurrentWeekCycleMenu;
use App\Mcp\Tools\Dashboard\Summary;
use App\Mcp\Tools\Expenses\ListExpenses;
use App\Mcp\Tools\Investments\Portfolio;
use App\Mcp\Tools\Iou\ListIou;
use App\Mcp\Tools\Jobs\Pipeline;
use App\Mcp\Tools\Notifications\ListNotifications;
use App\Mcp\Tools\Subscriptions\ListSubscriptions;
use App\Mcp\Tools\Warranties\ListWarranties;
use App\Models\AgentToken;
use App\Models\Contract;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\Iou;
use App\Models\JobApplication;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UtilityBill;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ToolsTest extends TestCase
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

    public function test_unauthorized_token_is_rejected_at_tool_layer(): void
    {
        // Replace the bound token with one that has no abilities.
        [$restricted] = AgentToken::issue($this->user, $this->tenant, 'restricted', ['subscriptions.list']);
        App::instance('agent.token', $restricted);

        LifeOsServer::tool(ListExpenses::class)
            ->assertHasErrors(['Agent token is not authorized to call [expenses.list].']);
    }

    public function test_cross_tenant_data_is_invisible(): void
    {
        $otherUser = User::factory()->create();
        $otherTenant = Tenant::factory()->create(['owner_id' => $otherUser->id]);
        $otherUser->forceFill(['current_tenant_id' => $otherTenant->id])->save();

        $this->actingAs($otherUser);
        Expense::factory()->create([
            'amount' => 999.99,
            'merchant' => 'Other Tenant Merchant',
            'currency' => 'EUR',
        ]);

        // Restore primary tenant context.
        $this->actingAs($this->user);
        Expense::factory()->create([
            'amount' => 11.11,
            'merchant' => 'My Merchant',
            'currency' => 'EUR',
        ]);

        LifeOsServer::tool(ListExpenses::class)
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.merchant', 'My Merchant')
                    ->etc();
            });
    }

    public function test_dashboard_summary(): void
    {
        Subscription::factory()->create(['status' => 'active', 'next_billing_date' => now()->addDays(5)]);
        Contract::factory()->create(['status' => 'active', 'end_date' => now()->addDays(10)]);

        LifeOsServer::tool(Summary::class)
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->has('totals')
                    ->has('upcoming')
                    ->has('alerts')
                    ->where('totals.subscriptions_active', 1)
                    ->where('totals.contracts_active', 1)
                    ->etc();
            });
    }

    public function test_expenses_list(): void
    {
        Expense::factory()->create([
            'amount' => 12.50,
            'merchant' => 'Lidl',
            'currency' => 'EUR',
            'category' => 'groceries',
            'expense_date' => now()->subDay(),
        ]);

        LifeOsServer::tool(ListExpenses::class, ['merchant' => 'Lidl'])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.merchant', 'Lidl')
                    ->where('items.0.currency', 'EUR')
                    ->etc();
            });
    }

    public function test_subscriptions_list(): void
    {
        Subscription::factory()->create([
            'service_name' => 'Netflix',
            'status' => 'active',
            'next_billing_date' => now()->addDays(2),
            'cost' => 9.99,
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
        ]);

        LifeOsServer::tool(ListSubscriptions::class, ['due_within_days' => 7])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.service_name', 'Netflix')
                    ->etc();
            });
    }

    public function test_investments_portfolio(): void
    {
        Investment::factory()->create([
            'name' => 'VWCE',
            'investment_type' => 'etf',
            'quantity' => 10,
            'purchase_price' => 100,
            'current_value' => 110,
            'currency' => 'EUR',
        ]);

        LifeOsServer::tool(Portfolio::class)
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->has('totals_by_currency.EUR')
                    ->where('totals_by_currency.EUR.market_value', 1100.0)
                    ->where('totals_by_currency.EUR.unrealized_gain_loss', 100.0)
                    ->etc();
            });
    }

    public function test_bills_upcoming(): void
    {
        UtilityBill::factory()->create([
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 50,
            'currency' => 'EUR',
            'due_date' => now()->addDays(3),
            'payment_status' => 'pending',
        ]);

        LifeOsServer::tool(UpcomingBills::class, ['within_days' => 7])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', fn ($count) => $count >= 1)
                    ->where('items.0.service_provider', 'EVN')
                    ->etc();
            });
    }

    public function test_contracts_list(): void
    {
        Contract::factory()->create([
            'title' => 'Office Lease',
            'counterparty' => 'Landlord Co',
            'status' => 'active',
            'end_date' => now()->addDays(15),
        ]);

        LifeOsServer::tool(ListContracts::class, ['expiring_within_days' => 30])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.title', 'Office Lease')
                    ->etc();
            });
    }

    public function test_warranties_list(): void
    {
        Warranty::factory()->create([
            'product_name' => 'Laptop',
            'brand' => 'Apple',
            'current_status' => 'active',
            'warranty_expiration_date' => now()->addDays(20),
        ]);

        LifeOsServer::tool(ListWarranties::class, ['expiring_within_days' => 60])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.product_name', 'Laptop')
                    ->etc();
            });
    }

    public function test_iou_list(): void
    {
        Iou::factory()->create([
            'type' => 'owe',
            'person_name' => 'John Doe',
            'amount' => 200,
            'amount_paid' => 50,
            'currency' => 'EUR',
            'status' => 'partially_paid',
        ]);

        LifeOsServer::tool(ListIou::class, ['direction' => 'owe'])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.person_name', 'John Doe')
                    ->where('items.0.remaining', 150.0)
                    ->etc();
            });
    }

    public function test_jobs_pipeline(): void
    {
        JobApplication::factory()->create([
            'company_name' => 'Acme',
            'job_title' => 'Engineer',
            'remote' => true,
            'applied_at' => now()->subDays(3),
        ]);

        LifeOsServer::tool(Pipeline::class, ['remote_only' => true])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', 1)
                    ->where('items.0.company_name', 'Acme')
                    ->etc();
            });
    }

    public function test_cycle_menu_current_week(): void
    {
        $menu = CycleMenu::factory()->create([
            'name' => 'Standard',
            'is_active' => true,
            'starts_on' => now()->subDays(2)->toDateString(),
            'cycle_length_days' => 7,
        ]);
        $day = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $menu->id,
            'day_index' => 2,
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'title' => 'Pasta',
        ]);

        LifeOsServer::tool(CurrentWeekCycleMenu::class)
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->has('menu')
                    ->where('menu.name', 'Standard')
                    ->has('week', 7)
                    ->etc();
            });
    }

    public function test_notifications_list(): void
    {
        $this->user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'TestNotification',
            'data' => ['title' => 'Hello'],
        ]);

        LifeOsServer::tool(ListNotifications::class, ['limit' => 10])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json) {
                $json->where('count', fn ($count) => $count >= 1)->etc();
            });
    }
}
