<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\CreateContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateContractTest extends TestCase
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
    public function it_creates_a_contract(): void
    {
        $tool = new CreateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'title' => 'Office Lease',
            'counterparty' => 'Building Corp',
            'contract_type' => 'lease',
            'start_date' => '2026-01-01',
            'end_date' => '2027-01-01',
            'contract_value' => 12000,
        ]));

        $this->assertStringContainsString('Created contract', $result);
        $this->assertStringContainsString('Office Lease', $result);
        $this->assertDatabaseHas('contracts', [
            'tenant_id' => $this->tenantId,
            'title' => 'Office Lease',
            'counterparty' => 'Building Corp',
        ]);
    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $tool = new CreateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('Validation failed', $result);
    }

    #[Test]
    public function it_validates_end_date_after_start(): void
    {
        $tool = new CreateContract($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'title' => 'Test',
            'counterparty' => 'Test Corp',
            'contract_type' => 'service',
            'start_date' => '2026-06-01',
            'end_date' => '2026-01-01',
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
