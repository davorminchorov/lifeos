<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\FileWarrantyClaim;
use App\Models\Warranty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileWarrantyClaimTest extends TestCase
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
    public function it_files_a_warranty_claim(): void
    {
        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'MacBook Pro',
            'current_status' => 'active',
            'claim_history' => [],
            'warranty_expiration_date' => now()->addYear(),
        ]);

        $tool = new FileWarrantyClaim($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'product_name' => 'MacBook',
            'reason' => 'Screen flickering',
            'description' => 'Screen flickers when opening lid',
        ]));

        $this->assertStringContainsString('Filed warranty claim', $result);
        $this->assertStringContainsString('MacBook Pro', $result);
        $this->assertDatabaseHas('warranties', [
            'product_name' => 'MacBook Pro',
            'current_status' => 'claimed',
        ]);
    }

    #[Test]
    public function it_returns_error_for_unknown_warranty(): void
    {
        $tool = new FileWarrantyClaim($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'product_name' => 'Nonexistent',
            'reason' => 'Broken',
        ]));

        $this->assertStringContainsString('No warranty found', $result);
    }

    #[Test]
    public function it_validates_required_reason(): void
    {
        Warranty::factory()->create([
            'tenant_id' => $this->tenantId,
            'user_id' => $this->userId,
            'product_name' => 'Test Product',
            'current_status' => 'active',
        ]);

        $tool = new FileWarrantyClaim($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([
            'product_name' => 'Test Product',
        ]));

        $this->assertStringContainsString('Validation failed', $result);
    }
}
