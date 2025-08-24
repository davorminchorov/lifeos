<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Budget::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Food & Dining',
            'Transportation',
            'Shopping',
            'Entertainment',
            'Bills & Utilities',
            'Health & Fitness',
            'Travel',
            'Education',
            'Personal Care',
            'Gifts & Donations',
            'Home & Garden',
            'Technology',
            'Clothing',
            'Groceries',
            'Gas',
        ];

        $periods = ['monthly', 'quarterly', 'yearly', 'custom'];
        $period = $this->faker->randomElement($periods);

        // Set dates based on period
        $dates = $this->getPeriodDates($period);

        return [
            'user_id' => User::factory(),
            'category' => $this->faker->randomElement($categories),
            'budget_period' => $period,
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => $this->faker->randomElement(['MKD', 'EUR', 'USD']),
            'start_date' => $dates['start_date'],
            'end_date' => $dates['end_date'],
            'is_active' => $this->faker->boolean(85), // 85% chance of being active
            'rollover_unused' => $this->faker->boolean(30), // 30% chance of rollover
            'alert_threshold' => $this->faker->numberBetween(60, 90),
            'notes' => $this->faker->optional(0.4)->sentence(10),
        ];
    }

    /**
     * Get period dates based on budget period type.
     */
    private function getPeriodDates(string $period): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'monthly':
                return [
                    'start_date' => $now->copy()->startOfMonth()->toDateString(),
                    'end_date' => $now->copy()->endOfMonth()->toDateString(),
                ];
            case 'quarterly':
                return [
                    'start_date' => $now->copy()->startOfQuarter()->toDateString(),
                    'end_date' => $now->copy()->endOfQuarter()->toDateString(),
                ];
            case 'yearly':
                return [
                    'start_date' => $now->copy()->startOfYear()->toDateString(),
                    'end_date' => $now->copy()->endOfYear()->toDateString(),
                ];
            case 'custom':
            default:
                $startDate = $this->faker->dateTimeBetween('-2 months', '+1 month');
                $endDate = Carbon::parse($startDate)->addDays($this->faker->numberBetween(30, 365));
                return [
                    'start_date' => Carbon::parse($startDate)->toDateString(),
                    'end_date' => $endDate->toDateString(),
                ];
        }
    }

    /**
     * Indicate that the budget is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the budget is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a monthly budget.
     */
    public function monthly(): static
    {
        return $this->state(function (array $attributes) {
            $now = Carbon::now();
            return [
                'budget_period' => 'monthly',
                'start_date' => $now->copy()->startOfMonth()->toDateString(),
                'end_date' => $now->copy()->endOfMonth()->toDateString(),
            ];
        });
    }

    /**
     * Create a quarterly budget.
     */
    public function quarterly(): static
    {
        return $this->state(function (array $attributes) {
            $now = Carbon::now();
            return [
                'budget_period' => 'quarterly',
                'start_date' => $now->copy()->startOfQuarter()->toDateString(),
                'end_date' => $now->copy()->endOfQuarter()->toDateString(),
            ];
        });
    }

    /**
     * Create a yearly budget.
     */
    public function yearly(): static
    {
        return $this->state(function (array $attributes) {
            $now = Carbon::now();
            return [
                'budget_period' => 'yearly',
                'start_date' => $now->copy()->startOfYear()->toDateString(),
                'end_date' => $now->copy()->endOfYear()->toDateString(),
            ];
        });
    }

    /**
     * Create a custom period budget.
     */
    public function customPeriod(Carbon $startDate, Carbon $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'budget_period' => 'custom',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
        ]);
    }

    /**
     * Create a budget for a specific category.
     */
    public function forCategory(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Create a budget with rollover enabled.
     */
    public function withRollover(): static
    {
        return $this->state(fn (array $attributes) => [
            'rollover_unused' => true,
        ]);
    }

    /**
     * Create a budget with high alert threshold.
     */
    public function highAlert(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_threshold' => $this->faker->numberBetween(90, 95),
        ]);
    }

    /**
     * Create a budget with low alert threshold.
     */
    public function lowAlert(): static
    {
        return $this->state(fn (array $attributes) => [
            'alert_threshold' => $this->faker->numberBetween(50, 70),
        ]);
    }

    /**
     * Create a high amount budget.
     */
    public function highAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 2000, 10000),
        ]);
    }

    /**
     * Create a low amount budget.
     */
    public function lowAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $this->faker->randomFloat(2, 50, 500),
        ]);
    }

    /**
     * Create a budget for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
