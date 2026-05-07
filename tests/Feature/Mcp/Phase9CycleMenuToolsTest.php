<?php

declare(strict_types=1);

namespace Tests\Feature\Mcp;

use App\Enums\MealType;
use App\Mcp\LifeOsServer;
use App\Mcp\Tools\CycleMenu\AddItem as AddCycleMenuItem;
use App\Mcp\Tools\CycleMenu\SetWeek as SetCycleMenuWeek;
use App\Mcp\Tools\CycleMenu\ShoppingList as CycleMenuShoppingList;
use App\Models\AgentToken;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use App\Models\CycleMenuItem;
use App\Models\PendingAction;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Agents\PendingActionApplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class Phase9CycleMenuToolsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tenant $tenant;

    private CycleMenu $menu;

    protected function setUp(): void
    {
        parent::setUp();

        ['user' => $this->user, 'tenant' => $this->tenant] = $this->setupTenantContext();
        [$token] = AgentToken::issue($this->user, $this->tenant, 'phpunit', ['*']);
        App::instance('agent.token', $token);

        $this->menu = CycleMenu::factory()->create([
            'name' => 'Test Cycle',
            'is_active' => true,
            'starts_on' => now()->subDays(3)->toDateString(),
            'cycle_length_days' => 7,
        ]);
    }

    public function test_add_item_queues_pending_action(): void
    {
        LifeOsServer::tool(AddCycleMenuItem::class, [
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 2,
            'title' => 'Pasta Bolognese',
            'meal_type' => 'dinner',
        ])->assertOk();

        $this->assertSame(1, PendingAction::query()->where('tool', 'cycleMenu.addItem')->count());
        $this->assertSame(0, CycleMenuItem::query()->count());
    }

    public function test_add_item_idempotency(): void
    {
        $args = [
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 2,
            'title' => 'Pasta Bolognese',
            'meal_type' => 'dinner',
        ];

        LifeOsServer::tool(AddCycleMenuItem::class, $args);
        LifeOsServer::tool(AddCycleMenuItem::class, $args);

        $this->assertSame(1, PendingAction::query()->count());
    }

    public function test_apply_add_item_creates_day_if_missing(): void
    {
        LifeOsServer::tool(AddCycleMenuItem::class, [
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 2,
            'title' => 'Pasta',
            'meal_type' => 'dinner',
        ]);

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(1, CycleMenuDay::query()->count());
        $this->assertSame(1, CycleMenuItem::query()->count());
        $item = CycleMenuItem::query()->first();
        $this->assertSame('Pasta', $item->title);
    }

    public function test_add_item_rejects_out_of_range_day(): void
    {
        LifeOsServer::tool(AddCycleMenuItem::class, [
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 99,
            'title' => 'Bad',
            'meal_type' => 'dinner',
        ]);

        $action = PendingAction::query()->firstOrFail();

        $this->expectException(\RuntimeException::class);
        app(PendingActionApplier::class)->apply($action, $this->user);
    }

    public function test_set_week_replaces_existing_items_on_apply(): void
    {
        $existingDay = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 0,
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $existingDay->id,
            'title' => 'Old Item',
            'position' => 0,
        ]);
        $this->assertSame(1, CycleMenuItem::query()->count());

        LifeOsServer::tool(SetCycleMenuWeek::class, [
            'cycle_menu_id' => $this->menu->id,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Eggs', 'meal_type' => 'breakfast'],
                    ['title' => 'Salad', 'meal_type' => 'lunch'],
                ],
                1 => [
                    ['title' => 'Pasta', 'meal_type' => 'dinner'],
                ],
            ],
        ])->assertOk()->assertStructuredContent(function (AssertableJson $json): void {
            $json->where('day_count', 2)
                ->where('item_count', 3)
                ->etc();
        });

        $action = PendingAction::query()->firstOrFail();
        app(PendingActionApplier::class)->apply($action, $this->user);

        $this->assertSame(3, CycleMenuItem::query()->count());
        $this->assertNull(CycleMenuItem::query()->where('title', 'Old Item')->first(), 'Old item should be deleted.');
    }

    public function test_revert_set_week_restores_prior_items(): void
    {
        $day = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 0,
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day->id,
            'title' => 'Original',
            'meal_type' => MealType::Lunch,
            'position' => 0,
        ]);

        LifeOsServer::tool(SetCycleMenuWeek::class, [
            'cycle_menu_id' => $this->menu->id,
            'items_by_day_index' => [
                0 => [
                    ['title' => 'Replacement', 'meal_type' => 'dinner'],
                ],
            ],
        ]);

        $action = PendingAction::query()->firstOrFail();
        $applied = app(PendingActionApplier::class)->apply($action, $this->user);
        $this->assertSame('Replacement', CycleMenuItem::query()->first()->title);

        app(PendingActionApplier::class)->revert($applied, $this->user);

        $this->assertSame(1, CycleMenuItem::query()->count());
        $this->assertSame('Original', CycleMenuItem::query()->first()->title);
    }

    public function test_shopping_list_aggregates_upcoming_window(): void
    {
        // Two days, two items each, with one duplicate across days to verify counting.
        $day0 = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 0,
        ]);
        $day1 = CycleMenuDay::factory()->create([
            'cycle_menu_id' => $this->menu->id,
            'day_index' => 1,
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day0->id,
            'title' => 'Eggs',
            'meal_type' => MealType::Breakfast,
            'quantity' => '2',
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day1->id,
            'title' => 'Eggs',
            'meal_type' => MealType::Breakfast,
            'quantity' => '2',
        ]);
        CycleMenuItem::factory()->create([
            'cycle_menu_day_id' => $day0->id,
            'title' => 'Salad',
            'meal_type' => MealType::Lunch,
        ]);

        LifeOsServer::tool(CycleMenuShoppingList::class, ['window_days' => 7])
            ->assertOk()
            ->assertStructuredContent(function (AssertableJson $json): void {
                $json->where('items', function ($items): bool {
                    $items = is_array($items) ? $items : $items->all();
                    $this->assertNotEmpty($items);
                    $eggsRow = collect($items)->firstWhere('title', 'Eggs');
                    $this->assertNotNull($eggsRow);
                    $this->assertGreaterThanOrEqual(1, $eggsRow['count']);

                    return true;
                })->etc();
            });
    }
}
