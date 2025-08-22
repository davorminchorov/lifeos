<?php

namespace Tests\Unit;

use App\Models\Investment;
use App\Models\InvestmentDividend;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentDividendModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_investment_dividend_has_fillable_attributes(): void
    {
        $fillable = [
            'investment_id',
            'amount',
            'record_date',
            'payment_date',
            'ex_dividend_date',
            'dividend_type',
            'frequency',
            'dividend_per_share',
            'shares_held',
            'tax_withheld',
            'currency',
            'reinvested',
            'notes',
        ];
        $dividend = new InvestmentDividend;

        $this->assertEquals($fillable, $dividend->getFillable());
    }

    public function test_investment_dividend_casts_attributes_correctly(): void
    {
        $dividend = new InvestmentDividend;
        $casts = $dividend->getCasts();

        $this->assertArrayHasKey('record_date', $casts);
        $this->assertArrayHasKey('payment_date', $casts);
        $this->assertArrayHasKey('ex_dividend_date', $casts);
        $this->assertArrayHasKey('amount', $casts);
        $this->assertArrayHasKey('dividend_per_share', $casts);
        $this->assertArrayHasKey('shares_held', $casts);
        $this->assertArrayHasKey('tax_withheld', $casts);
        $this->assertArrayHasKey('reinvested', $casts);
        $this->assertEquals('date', $casts['record_date']);
        $this->assertEquals('date', $casts['payment_date']);
        $this->assertEquals('date', $casts['ex_dividend_date']);
        $this->assertEquals('decimal:2', $casts['amount']);
        $this->assertEquals('decimal:8', $casts['dividend_per_share']);
        $this->assertEquals('decimal:8', $casts['shares_held']);
        $this->assertEquals('decimal:2', $casts['tax_withheld']);
        $this->assertEquals('boolean', $casts['reinvested']);
    }

    public function test_investment_dividend_belongs_to_investment(): void
    {
        $dividend = new InvestmentDividend;
        $relationship = $dividend->investment();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('investment_id', $relationship->getForeignKeyName());
    }

    public function test_can_create_investment_dividend_with_investment(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $dividend = InvestmentDividend::factory()->create([
            'investment_id' => $investment->id,
            'amount' => 100.50,
            'currency' => 'USD',
        ]);

        $this->assertInstanceOf(InvestmentDividend::class, $dividend);
        $this->assertEquals($investment->id, $dividend->investment_id);
        $this->assertEquals(100.50, $dividend->amount);
        $this->assertEquals('USD', $dividend->currency);
    }

    public function test_investment_dividend_factory_creates_valid_dividend(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);
        $dividend = InvestmentDividend::factory()->create(['investment_id' => $investment->id]);

        $this->assertInstanceOf(InvestmentDividend::class, $dividend);
        $this->assertNotNull($dividend->investment_id);
        $this->assertNotNull($dividend->amount);
        $this->assertNotNull($dividend->record_date);
        $this->assertNotNull($dividend->dividend_type);
        $this->assertNotNull($dividend->created_at);
        $this->assertNotNull($dividend->updated_at);
    }

    public function test_investment_dividend_factory_can_create_with_custom_attributes(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $dividendData = [
            'investment_id' => $investment->id,
            'amount' => 250.75,
            'currency' => 'EUR',
            'dividend_type' => 'ordinary',
        ];

        $dividend = InvestmentDividend::factory()->create($dividendData);

        $this->assertEquals($investment->id, $dividend->investment_id);
        $this->assertEquals(250.75, $dividend->amount);
        $this->assertEquals('EUR', $dividend->currency);
        $this->assertEquals('ordinary', $dividend->dividend_type);
    }

    public function test_dividend_amount_is_required(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        InvestmentDividend::create([
            'investment_id' => $investment->id,
            'record_date' => now(),
            'dividend_type' => 'ordinary',
            // missing amount
        ]);
    }

    public function test_investment_id_is_required(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        InvestmentDividend::create([
            'amount' => 100.00,
            'record_date' => now(),
            'dividend_type' => 'ordinary',
            // missing investment_id
        ]);
    }

    public function test_can_access_related_investment(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);
        $dividend = InvestmentDividend::factory()->create(['investment_id' => $investment->id]);

        $this->assertInstanceOf(Investment::class, $dividend->investment);
        $this->assertEquals($investment->id, $dividend->investment->id);
        $this->assertEquals($investment->name, $dividend->investment->name);
    }

    public function test_dividend_type_can_be_set(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $dividend = InvestmentDividend::factory()->create([
            'investment_id' => $investment->id,
            'dividend_type' => 'special',
        ]);

        $this->assertEquals('special', $dividend->dividend_type);
    }

    public function test_tax_withheld_can_be_zero(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $dividend = InvestmentDividend::factory()->create([
            'investment_id' => $investment->id,
            'tax_withheld' => 0.00,
        ]);

        $this->assertEquals(0.00, $dividend->tax_withheld);
    }

    public function test_payment_date_can_be_different_from_record_date(): void
    {
        $user = User::factory()->create();
        $investment = Investment::factory()->create(['user_id' => $user->id]);

        $recordDate = now()->subDays(5);
        $paymentDate = now()->addDays(2);

        $dividend = InvestmentDividend::factory()->create([
            'investment_id' => $investment->id,
            'record_date' => $recordDate,
            'payment_date' => $paymentDate,
        ]);

        $this->assertEquals($recordDate->format('Y-m-d'), $dividend->record_date->format('Y-m-d'));
        $this->assertEquals($paymentDate->format('Y-m-d'), $dividend->payment_date->format('Y-m-d'));
    }
}
