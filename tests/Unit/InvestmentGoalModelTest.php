<?php

namespace Tests\Unit;

use App\Models\InvestmentGoal;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentGoalModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_investment_goal_has_fillable_attributes(): void
    {
        $fillable = [
            'user_id', 'title', 'description', 'target_amount', 'current_progress',
            'target_date', 'status', 'created_at', 'updated_at'
        ];
        $goal = new InvestmentGoal();

        $this->assertEquals($fillable, $goal->getFillable());
    }

    public function test_investment_goal_casts_attributes_correctly(): void
    {
        $goal = new InvestmentGoal();
        $casts = $goal->getCasts();

        $this->assertArrayHasKey('target_amount', $casts);
        $this->assertArrayHasKey('current_progress', $casts);
        $this->assertArrayHasKey('target_date', $casts);

        $this->assertEquals('decimal:2', $casts['target_amount']);
        $this->assertEquals('decimal:2', $casts['current_progress']);
        $this->assertEquals('date', $casts['target_date']);
    }

    public function test_investment_goal_belongs_to_user(): void
    {
        $goal = new InvestmentGoal();
        $relationship = $goal->user();

        $this->assertInstanceOf(BelongsTo::class, $relationship);
        $this->assertEquals('user_id', $relationship->getForeignKeyName());
    }

    public function test_progress_percentage_attribute(): void
    {
        $user = User::factory()->create();

        // Test 50% progress
        $goal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 500.00
        ]);
        $this->assertEquals(50, $goal->progress_percentage);

        // Test 100% progress
        $goalComplete = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1000.00
        ]);
        $this->assertEquals(100, $goalComplete->progress_percentage);

        // Test over 100% progress (should cap at 100)
        $goalOver = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1200.00
        ]);
        $this->assertEquals(100, $goalOver->progress_percentage);

        // Test zero target (should return 0)
        $goalZero = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 0.00,
            'current_progress' => 100.00
        ]);
        $this->assertEquals(0, $goalZero->progress_percentage);
    }

    public function test_is_achieved_attribute(): void
    {
        $user = User::factory()->create();

        $achievedGoal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1000.00
        ]);
        $this->assertTrue($achievedGoal->is_achieved);

        $overAchievedGoal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1200.00
        ]);
        $this->assertTrue($overAchievedGoal->is_achieved);

        $notAchievedGoal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 800.00
        ]);
        $this->assertFalse($notAchievedGoal->is_achieved);
    }

    public function test_remaining_amount_attribute(): void
    {
        $user = User::factory()->create();

        $goal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 300.00
        ]);
        $this->assertEquals(700.00, $goal->remaining_amount);

        $completedGoal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1000.00
        ]);
        $this->assertEquals(0, $completedGoal->remaining_amount);

        $overAchievedGoal = InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1200.00
        ]);
        $this->assertEquals(0, $overAchievedGoal->remaining_amount);
    }

    public function test_scope_active(): void
    {
        $user = User::factory()->create();
        InvestmentGoal::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        InvestmentGoal::factory()->create(['user_id' => $user->id, 'status' => 'achieved']);
        InvestmentGoal::factory()->create(['user_id' => $user->id, 'status' => 'active']);

        $activeGoals = InvestmentGoal::active()->get();

        $this->assertCount(2, $activeGoals);
        $activeGoals->each(function ($goal) {
            $this->assertEquals('active', $goal->status);
        });
    }

    public function test_scope_achieved(): void
    {
        $user = User::factory()->create();
        InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1000.00
        ]);
        InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 500.00
        ]);
        InvestmentGoal::factory()->create([
            'user_id' => $user->id,
            'target_amount' => 1000.00,
            'current_progress' => 1200.00
        ]);

        $achievedGoals = InvestmentGoal::achieved()->get();

        $this->assertCount(2, $achievedGoals);
        $achievedGoals->each(function ($goal) {
            $this->assertTrue($goal->current_progress >= $goal->target_amount);
        });
    }

    public function test_investment_goal_factory_creates_valid_goal(): void
    {
        $goal = InvestmentGoal::factory()->create();

        $this->assertInstanceOf(InvestmentGoal::class, $goal);
        $this->assertNotNull($goal->user_id);
        $this->assertNotNull($goal->title);
        $this->assertNotNull($goal->target_amount);
        $this->assertNotNull($goal->current_progress);
        $this->assertNotNull($goal->created_at);
        $this->assertNotNull($goal->updated_at);
    }

    public function test_investment_goal_factory_can_create_with_custom_attributes(): void
    {
        $goalData = [
            'title' => 'Emergency Fund',
            'target_amount' => 10000.00,
            'current_progress' => 2500.00,
        ];

        $goal = InvestmentGoal::factory()->create($goalData);

        $this->assertEquals('Emergency Fund', $goal->title);
        $this->assertEquals(10000.00, $goal->target_amount);
        $this->assertEquals(2500.00, $goal->current_progress);
    }
}
