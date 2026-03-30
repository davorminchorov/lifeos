<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\UpdateContract;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateContractTest extends TestCase
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
    public function it_updates_contract_status(): void
    {
        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Office Lease',
            'status' => 'active',
        ]);

        $tool = new UpdateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'title' => 'Office',
            'new_status' => 'terminated',
        ]));

        $this->assertStringContainsString('Updated contract', $result);
        $this->assertStringContainsString('status to terminated', $result);
        $this->assertDatabaseHas('contracts', [
            'title' => 'Office Lease',
            'status' => 'terminated',
        ]);
    }

    #[Test]
    public function it_returns_error_for_unknown_contract(): void
    {
        $tool = new UpdateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'title' => 'Nonexistent',
            'new_status' => 'terminated',
        ]));

        $this->assertStringContainsString('No contract found', $result);
    }

    #[Test]
    public function it_returns_error_when_no_changes(): void
    {
        Contract::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'title' => 'Test Contract',
        ]);

        $tool = new UpdateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['title' => 'Test Contract']));

        $this->assertStringContainsString('No changes provided', $result);
    }
}
