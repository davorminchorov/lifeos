<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\QueryHolidays;
use App\Models\Holiday;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryHolidaysTest extends TestCase
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
    public function it_returns_no_results_when_empty(): void
    {
        $tool = new QueryHolidays($this->userId, $this->tenantId);
        $result = $tool->handle(new Request([]));

        $this->assertStringContainsString('No holidays found', $result);
    }

    #[Test]
    public function it_filters_by_country(): void
    {
        Holiday::create([
            'tenant_id' => $this->tenantId,
            'country' => 'MK',
            'date' => '2026-01-01',
            'name' => 'New Year MK',
        ]);

        Holiday::create([
            'tenant_id' => $this->tenantId,
            'country' => 'US',
            'date' => '2026-01-01',
            'name' => 'New Year US',
        ]);

        $tool = new QueryHolidays($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['country' => 'MK']));

        $this->assertStringContainsString('New Year MK', $result);
        $this->assertStringNotContainsString('New Year US', $result);
    }

    #[Test]
    public function it_filters_by_name(): void
    {
        Holiday::create([
            'tenant_id' => $this->tenantId,
            'country' => 'MK',
            'date' => '2026-05-01',
            'name' => 'Labour Day',
        ]);

        $tool = new QueryHolidays($this->userId, $this->tenantId);
        $result = $tool->handle(new Request(['name' => 'Labour']));

        $this->assertStringContainsString('Labour Day', $result);
    }
}
