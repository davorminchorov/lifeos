<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryCycleMenus;
use App\Models\CycleMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryCycleMenusTest extends TestCase
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
    public function it_returns_no_results_message_when_empty(): void
    {
        $tool = new QueryCycleMenus($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No cycle menus found', $result);
    }

    #[Test]
    public function it_queries_cycle_menus_by_name(): void
    {
        CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Weekly Meal Plan',
            'is_active' => true,
        ]);

        CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Holiday Menu',
            'is_active' => true,
        ]);

        $tool = new QueryCycleMenus($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['name' => 'Weekly']));

        $this->assertStringContainsString('Weekly Meal Plan', $result);
        $this->assertStringNotContainsString('Holiday Menu', $result);
    }

    #[Test]
    public function it_filters_by_active_status(): void
    {
        CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Active Menu',
            'is_active' => true,
        ]);

        CycleMenu::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'name' => 'Inactive Menu',
            'is_active' => false,
        ]);

        $tool = new QueryCycleMenus($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['is_active' => true]));

        $this->assertStringContainsString('Active Menu', $result);
        $this->assertStringNotContainsString('Inactive Menu', $result);
    }
}
