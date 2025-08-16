<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_user_can_view_contracts_index()
    {
        Contract::factory()->count(3)->for($this->user)->create();

        $response = $this->get(route('contracts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.index');
        $response->assertViewHas('contracts');
    }

    public function test_user_can_create_contract()
    {
        $contractData = [
            'contract_type' => 'service',
            'title' => 'IT Support Contract',
            'counterparty' => 'Tech Solutions Inc.',
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'notice_period_days' => 30,
            'auto_renewal' => true,
            'contract_value' => 12000.00,
            'payment_terms' => 'Monthly',
            'key_obligations' => 'Provide 24/7 IT support',
            'penalties' => 'Late payment fee: $100',
            'termination_clauses' => '30 days notice required',
            'notes' => 'Critical business contract',
            'status' => 'active',
        ];

        $response = $this->post(route('contracts.store'), $contractData);

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', [
            'user_id' => $this->user->id,
            'title' => 'IT Support Contract',
            'contract_type' => 'service',
        ]);
    }

    public function test_user_can_view_contract_details()
    {
        $contract = Contract::factory()->for($this->user)->create([
            'title' => 'Test Contract',
        ]);

        $response = $this->get(route('contracts.show', $contract));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.show');
        $response->assertViewHas('contract', $contract);
        $response->assertSee('Test Contract');
    }

    public function test_user_can_update_contract()
    {
        $contract = Contract::factory()->for($this->user)->create();

        $updatedData = [
            'contract_type' => 'lease',
            'title' => 'Updated Contract Title',
            'counterparty' => $contract->counterparty,
            'start_date' => $contract->start_date->format('Y-m-d'),
            'end_date' => $contract->end_date?->format('Y-m-d'),
            'notice_period_days' => 60,
            'auto_renewal' => false,
            'contract_value' => 15000.00,
            'payment_terms' => 'Quarterly',
            'status' => 'active',
        ];

        $response = $this->put(route('contracts.update', $contract), $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'title' => 'Updated Contract Title',
            'notice_period_days' => 60,
            'auto_renewal' => false,
        ]);
    }

    public function test_user_can_delete_contract()
    {
        $contract = Contract::factory()->for($this->user)->create();

        $response = $this->delete(route('contracts.destroy', $contract));

        $response->assertRedirect();
        $this->assertSoftDeleted('contracts', ['id' => $contract->id]);
    }

    public function test_user_can_terminate_contract()
    {
        $contract = Contract::factory()->for($this->user)->create([
            'status' => 'active',
        ]);

        $response = $this->post(route('contracts.terminate', $contract), [
            'termination_reason' => 'Contract completed successfully',
        ]);

        $response->assertRedirect();
        $contract->refresh();
        $this->assertEquals('terminated', $contract->status);
    }

    public function test_user_can_renew_contract()
    {
        $contract = Contract::factory()->for($this->user)->create([
            'end_date' => now()->addDays(30),
            'status' => 'active',
        ]);

        $response = $this->post(route('contracts.renew', $contract), [
            'new_end_date' => now()->addYear()->format('Y-m-d'),
            'renewal_terms' => 'Standard renewal terms',
        ]);

        $response->assertRedirect();
        $contract->refresh();
        $this->assertEquals(now()->addYear()->format('Y-m-d'), $contract->end_date->format('Y-m-d'));
    }

    public function test_contracts_can_be_filtered_by_type()
    {
        Contract::factory()->for($this->user)->create(['contract_type' => 'service']);
        Contract::factory()->for($this->user)->create(['contract_type' => 'lease']);

        $response = $this->get(route('contracts.index', ['contract_type' => 'service']));

        $response->assertStatus(200);
    }

    public function test_contracts_can_be_searched()
    {
        Contract::factory()->for($this->user)->create(['title' => 'IT Support Contract']);
        Contract::factory()->for($this->user)->create(['title' => 'Cleaning Service']);

        $response = $this->get(route('contracts.index', ['search' => 'IT Support']));

        $response->assertStatus(200);
    }

    public function test_user_can_view_expiring_contracts()
    {
        // Create contracts expiring soon
        Contract::factory()->for($this->user)->create([
            'end_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        $response = $this->get(route('contracts.index', ['expiring_soon' => 30]));

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_users_contracts()
    {
        $otherUser = User::factory()->create();
        $otherContract = Contract::factory()->for($otherUser)->create();

        $response = $this->get(route('contracts.show', $otherContract));

        $response->assertStatus(403);
    }

    public function test_contract_validation_rules()
    {
        $response = $this->post(route('contracts.store'), []);

        $response->assertSessionHasErrors(['contract_type', 'title', 'counterparty', 'start_date']);
    }

    public function test_contract_scopes_work_correctly()
    {
        // Test active scope
        $activeContract = Contract::factory()->for($this->user)->create(['status' => 'active']);
        $expiredContract = Contract::factory()->for($this->user)->create(['status' => 'expired']);

        $activeContracts = Contract::active()->get();
        $this->assertTrue($activeContracts->contains($activeContract));
        $this->assertFalse($activeContracts->contains($expiredContract));

        // Test expiring soon scope
        $expiringSoon = Contract::factory()->for($this->user)->create([
            'end_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        $expiringContracts = Contract::expiringSoon(30)->get();
        $this->assertTrue($expiringContracts->contains($expiringSoon));
    }

    public function test_contract_attributes_work_correctly()
    {
        $contract = Contract::factory()->for($this->user)->create([
            'end_date' => now()->addDays(30),
            'notice_period_days' => 30,
        ]);

        $this->assertEquals(30, $contract->days_until_expiration);
        $this->assertFalse($contract->is_expired);
        $this->assertEquals(
            now()->format('Y-m-d'),
            $contract->notice_deadline->format('Y-m-d')
        );
    }

}
