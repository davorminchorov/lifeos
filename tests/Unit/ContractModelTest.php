<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_belongs_to_user()
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $contract->user);
        $this->assertEquals($user->id, $contract->user->id);
    }

    public function test_active_scope_filters_active_contracts()
    {
        $user = User::factory()->create();
        $activeContract = Contract::factory()->for($user)->create(['status' => 'active']);
        $expiredContract = Contract::factory()->for($user)->create(['status' => 'expired']);
        $terminatedContract = Contract::factory()->for($user)->create(['status' => 'terminated']);

        $activeContracts = Contract::active()->get();

        $this->assertTrue($activeContracts->contains($activeContract));
        $this->assertFalse($activeContracts->contains($expiredContract));
        $this->assertFalse($activeContracts->contains($terminatedContract));
    }

    public function test_expiring_soon_scope_filters_correctly()
    {
        $user = User::factory()->create();

        // Contract expiring in 15 days
        $expiringSoon = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(15),
            'status' => 'active',
        ]);

        // Contract expiring in 45 days
        $expiringLater = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(45),
            'status' => 'active',
        ]);

        // Contract without end date
        $noEndDate = Contract::factory()->for($user)->create([
            'end_date' => null,
            'status' => 'active',
        ]);

        $expiringContracts = Contract::expiringSoon(30)->get();

        $this->assertTrue($expiringContracts->contains($expiringSoon));
        $this->assertFalse($expiringContracts->contains($expiringLater));
        $this->assertFalse($expiringContracts->contains($noEndDate));
    }

    public function test_requiring_notice_scope_filters_correctly()
    {
        $user = User::factory()->create();

        // Contract requiring notice (30 days before end date)
        $requiresNotice = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(25),
            'notice_period_days' => 30,
            'status' => 'active',
        ]);

        // Contract not requiring notice yet
        $noNoticeYet = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(60),
            'notice_period_days' => 30,
            'status' => 'active',
        ]);

        // Contract without notice period
        $noNoticePeriod = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(25),
            'notice_period_days' => null,
            'status' => 'active',
        ]);

        $contractsRequiringNotice = Contract::requiringNotice()->get();

        $this->assertTrue($contractsRequiringNotice->contains($requiresNotice));
        $this->assertFalse($contractsRequiringNotice->contains($noNoticeYet));
        $this->assertFalse($contractsRequiringNotice->contains($noNoticePeriod));
    }

    public function test_is_expired_attribute_works_correctly()
    {
        $user = User::factory()->create();

        $expiredContract = Contract::factory()->for($user)->create([
            'end_date' => now()->subDays(10),
        ]);

        $activeContract = Contract::factory()->for($user)->create([
            'end_date' => now()->addDays(30),
        ]);

        $noEndDateContract = Contract::factory()->for($user)->create([
            'end_date' => null,
        ]);

        $this->assertTrue($expiredContract->is_expired);
        $this->assertFalse($activeContract->is_expired);
        $this->assertFalse($noEndDateContract->is_expired);
    }

    public function test_days_until_expiration_attribute_works_correctly()
    {
        $user = User::factory()->create();

        $today = now()->startOfDay();
        $contractExpiringSoon = Contract::factory()->for($user)->create([
            'end_date' => $today->copy()->addDays(15),
        ]);

        $expiredContract = Contract::factory()->for($user)->create([
            'end_date' => $today->copy()->subDays(5),
        ]);

        $noEndDateContract = Contract::factory()->for($user)->create([
            'end_date' => null,
        ]);

        $this->assertEquals(15, $contractExpiringSoon->days_until_expiration);
        $this->assertEquals(-5, $expiredContract->days_until_expiration);
        $this->assertNull($noEndDateContract->days_until_expiration);
    }

    public function test_notice_deadline_attribute_works_correctly()
    {
        $user = User::factory()->create();
        $endDate = now()->addDays(60);

        $contractWithNotice = Contract::factory()->for($user)->create([
            'end_date' => $endDate,
            'notice_period_days' => 30,
        ]);

        $contractWithoutNotice = Contract::factory()->for($user)->create([
            'end_date' => $endDate,
            'notice_period_days' => null,
        ]);

        $contractWithoutEndDate = Contract::factory()->for($user)->create([
            'end_date' => null,
            'notice_period_days' => 30,
        ]);

        $expectedDeadline = $endDate->copy()->subDays(30);
        $this->assertEquals($expectedDeadline->format('Y-m-d'), $contractWithNotice->notice_deadline->format('Y-m-d'));
        $this->assertNull($contractWithoutNotice->notice_deadline);
        $this->assertNull($contractWithoutEndDate->notice_deadline);
    }

    public function test_contract_casts_work_correctly()
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->for($user)->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'notice_period_days' => 30,
            'auto_renewal' => true,
            'contract_value' => '15000.50',
            'performance_rating' => 4,
            'document_attachments' => ['contract.pdf', 'terms.pdf'],
            'renewal_history' => [['date' => '2024-01-01', 'action' => 'renewed']],
            'amendments' => [['date' => '2024-06-01', 'change' => 'Price increase']],
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $contract->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $contract->end_date);
        $this->assertIsInt($contract->notice_period_days);
        $this->assertIsBool($contract->auto_renewal);
        $this->assertIsFloat($contract->contract_value);
        $this->assertIsInt($contract->performance_rating);
        $this->assertIsArray($contract->document_attachments);
        $this->assertIsArray($contract->renewal_history);
        $this->assertIsArray($contract->amendments);
    }

    public function test_fillable_attributes_are_correct()
    {
        $fillable = [
            'user_id',
            'contract_type',
            'title',
            'counterparty',
            'start_date',
            'end_date',
            'notice_period_days',
            'auto_renewal',
            'contract_value',
            'payment_terms',
            'key_obligations',
            'penalties',
            'termination_clauses',
            'document_attachments',
            'performance_rating',
            'renewal_history',
            'amendments',
            'notes',
            'status',
        ];

        $contract = new Contract;
        $this->assertEquals($fillable, $contract->getFillable());
    }

    public function test_contract_factory_creates_valid_contracts()
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->for($user)->create();

        $this->assertNotNull($contract->contract_type);
        $this->assertNotNull($contract->title);
        $this->assertNotNull($contract->counterparty);
        $this->assertNotNull($contract->start_date);
        $this->assertNotNull($contract->status);
        $this->assertEquals($user->id, $contract->user_id);
    }
}
