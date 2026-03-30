<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\CreateCycleMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateCycleMenuTest extends TestCase
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
    public function it_creates_a_cycle_menu_with_days(): void
    {
        $tool = new CreateCycleMenu($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'name' => 'Weekly Plan',
            'cycle_length_days' => 7,
        ]));

        $this->assertStringContainsString('Created cycle menu', $result);
        $this->assertStringContainsString('Weekly Plan', $result);
        $this->assertDatabaseHas('cycle_menus', [
            'tenant_id' => $this->tenantId,
            'name' => 'Weekly Plan',
            'cycle_length_days' => 7,
        ]);
        $this->assertDatabaseCount('cycle_menu_days', 7);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $tool = new CreateCycleMenu($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('Validation failed', $result);
    }

    #[Test]
    public function it_validates_cycle_length_range(): void
    {
        $tool = new CreateCycleMenu($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'name' => 'Too Long',
            'cycle_length_days' => 500,
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
