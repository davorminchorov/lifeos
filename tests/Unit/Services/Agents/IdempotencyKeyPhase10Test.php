<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase10Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_same_week_collapses_regardless_of_body(): void
    {
        $a = $this->keys->for('digest.send', 1, [
            'week_starts_on' => '2026-05-04',
            'subject' => 'first attempt',
            'body_text' => 'hello',
        ]);
        $b = $this->keys->for('digest.send', 1, [
            'week_starts_on' => '2026-05-04',
            'subject' => 'second attempt with different body',
            'body_text' => 'totally different content',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_different_weeks_produce_different_keys(): void
    {
        $a = $this->keys->for('digest.send', 1, ['week_starts_on' => '2026-05-04']);
        $b = $this->keys->for('digest.send', 1, ['week_starts_on' => '2026-05-11']);

        $this->assertNotSame($a, $b);
    }

    public function test_cross_tenant_keys_differ(): void
    {
        $payload = ['week_starts_on' => '2026-05-04'];

        $this->assertNotSame(
            $this->keys->for('digest.send', 1, $payload),
            $this->keys->for('digest.send', 2, $payload),
        );
    }
}
