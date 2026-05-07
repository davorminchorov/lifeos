<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Services\Agents\IdempotencyKey;
use Tests\TestCase;

class IdempotencyKeyPhase4Test extends TestCase
{
    private IdempotencyKey $keys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keys = new IdempotencyKey;
    }

    public function test_subscriptions_create_keys_collide_on_same_natural_fields(): void
    {
        $a = $this->keys->for('subscriptions.create', 1, [
            'service_name' => 'Netflix',
            'currency' => 'EUR',
            'billing_cycle' => 'monthly',
        ]);

        $b = $this->keys->for('subscriptions.create', 1, [
            'service_name' => ' netflix ',
            'currency' => 'eur',
            'billing_cycle' => 'MONTHLY',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_contracts_create_keys_separate_by_counterparty_and_start(): void
    {
        $a = $this->keys->for('contracts.create', 1, [
            'title' => 'Office Lease',
            'counterparty' => 'Landlord A',
            'start_date' => '2026-01-01',
        ]);

        $b = $this->keys->for('contracts.create', 1, [
            'title' => 'Office Lease',
            'counterparty' => 'Landlord B',
            'start_date' => '2026-01-01',
        ]);

        $this->assertNotSame($a, $b);
    }

    public function test_warranties_create_keys_use_serial_when_present(): void
    {
        $a = $this->keys->for('warranties.create', 1, [
            'product_name' => 'Laptop',
            'serial_number' => 'ABC123',
            'purchase_date' => '2026-05-01',
        ]);

        $b = $this->keys->for('warranties.create', 1, [
            'product_name' => 'Laptop',
            'serial_number' => 'XYZ987',
            'purchase_date' => '2026-05-01',
        ]);

        $this->assertNotSame($a, $b, 'Different serials must produce different keys.');
    }

    public function test_iou_create_distinguishes_direction(): void
    {
        $owe = $this->keys->for('iou.create', 1, [
            'type' => 'owe',
            'person_name' => 'Alex',
            'amount' => 50,
            'currency' => 'EUR',
            'transaction_date' => '2026-05-01',
        ]);

        $owed = $this->keys->for('iou.create', 1, [
            'type' => 'owed',
            'person_name' => 'Alex',
            'amount' => 50,
            'currency' => 'EUR',
            'transaction_date' => '2026-05-01',
        ]);

        $this->assertNotSame($owe, $owed);
    }

    public function test_utility_bills_create_uses_period_and_amount(): void
    {
        $a = $this->keys->for('utilityBills.create', 1, [
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 100,
            'currency' => 'EUR',
            'due_date' => '2026-05-15',
            'bill_period_end' => '2026-04-30',
        ]);

        $b = $this->keys->for('utilityBills.create', 1, [
            'utility_type' => 'electricity',
            'service_provider' => 'EVN',
            'bill_amount' => 100,
            'currency' => 'EUR',
            'due_date' => '2026-06-15',
            'bill_period_end' => '2026-05-31',
        ]);

        $this->assertNotSame($a, $b, 'Different billing periods are different bills.');
    }

    public function test_jobs_update_status_keys_collide_per_message_per_status(): void
    {
        $a = $this->keys->for('jobs.updateStatus', 1, [
            'job_application_id' => 7,
            'status' => 'interviewing',
            'source_email_id' => 'gmail-123',
        ]);

        $b = $this->keys->for('jobs.updateStatus', 1, [
            'job_application_id' => 7,
            'status' => 'interviewing',
            'source_email_id' => 'gmail-123',
        ]);

        $this->assertSame($a, $b);
    }

    public function test_jobs_add_interview_keys_use_scheduled_slot(): void
    {
        $first = $this->keys->for('jobs.addInterview', 1, [
            'job_application_id' => 7,
            'scheduled_at' => '2026-05-10T14:00:00Z',
            'interview_type' => 'video',
        ]);

        $second = $this->keys->for('jobs.addInterview', 1, [
            'job_application_id' => 7,
            'scheduled_at' => '2026-05-10T15:00:00Z',
            'interview_type' => 'video',
        ]);

        $this->assertNotSame($first, $second);
    }
}
