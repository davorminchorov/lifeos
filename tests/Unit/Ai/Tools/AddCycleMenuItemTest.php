<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\AddCycleMenuItem;
use App\Models\CycleMenu;
use App\Models\CycleMenuDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AddCycleMenuItemTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;

    private int $tenantId;

    protected function setUp(): void
    {
        parent::setUp();
        ['user' => $user, 'tenant' => $tenant] = $this->setupTenantContext();
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
    }

    #[Test]
    public function it_adds_item_to_existing_menu_day(): void
    {
        $menu = CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Weekly Plan',
            'cycle_length_days' => 7,
        ]);

        CycleMenuDay::create([
            'tenant_id' => $this->tenantId,
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
        ]);

        $tool = new AddCycleMenuItem($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'menu_name' => 'Weekly',
            'day_index' => 0,
            'title' => 'Scrambled Eggs',
            'meal_type' => 'breakfast',
        ]));

        $this->assertStringContainsString('Added breakfast item', $result);
        $this->assertStringContainsString('Scrambled Eggs', $result);
        $this->assertDatabaseHas('cycle_menu_items', [
            'title' => 'Scrambled Eggs',
        ]);
    }

    #[Test]
    public function it_returns_error_for_invalid_menu(): void
    {
        $tool = new AddCycleMenuItem($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'menu_name' => 'Nonexistent',
            'day_index' => 0,
            'title' => 'Test',
            'meal_type' => 'lunch',
        ]));

        $this->assertStringContainsString('No cycle menu found', $result);
    }

    #[Test]
    public function it_returns_error_for_invalid_meal_type(): void
    {
        $menu = CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Test Menu',
            'cycle_length_days' => 7,
        ]);

        CycleMenuDay::create([
            'tenant_id' => $this->tenantId,
            'cycle_menu_id' => $menu->id,
            'day_index' => 0,
        ]);

        $tool = new AddCycleMenuItem($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'menu_name' => 'Test Menu',
            'day_index' => 0,
            'title' => 'Something',
            'meal_type' => 'brunch',
        ]));

        $this->assertStringContainsString('Invalid meal type', $result);
    }
}
