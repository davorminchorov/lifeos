<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['food', 'transport', 'entertainment', 'shopping', 'healthcare', 'utilities', 'education', 'travel', 'home', 'insurance'];

        $subcategoriesByCategory = [
            'food' => ['restaurants', 'groceries', 'coffee', 'delivery', 'fast_food'],
            'transport' => ['gas', 'parking', 'public_transport', 'rideshare', 'maintenance'],
            'entertainment' => ['movies', 'concerts', 'games', 'books', 'streaming'],
            'shopping' => ['clothing', 'electronics', 'home_goods', 'gifts', 'personal_care'],
            'healthcare' => ['doctor', 'pharmacy', 'dental', 'vision', 'therapy'],
            'utilities' => ['electricity', 'gas', 'water', 'internet', 'phone'],
            'education' => ['tuition', 'books', 'courses', 'supplies', 'training'],
            'travel' => ['flights', 'hotels', 'car_rental', 'meals', 'activities'],
            'home' => ['rent', 'mortgage', 'repairs', 'furniture', 'cleaning'],
            'insurance' => ['health', 'auto', 'home', 'life', 'disability']
        ];

        $merchants = [
            'Walmart', 'Target', 'Starbucks', 'McDonald\'s', 'Amazon', 'Best Buy',
            'Home Depot', 'CVS Pharmacy', 'Shell', 'Uber', 'Netflix', 'Spotify'
        ];

        $paymentMethods = ['Credit Card', 'Debit Card', 'Cash', 'PayPal', 'Apple Pay', 'Google Pay'];
        $expenseTypes = ['business', 'personal'];
        $statuses = ['pending', 'confirmed', 'reimbursed'];
        $recurringSchedules = ['daily', 'weekly', 'monthly', 'yearly'];

        $category = $this->faker->randomElement($categories);
        $subcategory = $this->faker->optional()->randomElement($subcategoriesByCategory[$category] ?? []);
        $isRecurring = $this->faker->boolean(20);

        return [
            'user_id' => \App\Models\User::factory(),
            'amount' => $this->faker->randomFloat(2, 1.99, 999.99),
            'currency' => 'USD',
            'category' => $category,
            'subcategory' => $subcategory,
            'expense_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'description' => $this->faker->sentence(4),
            'merchant' => $this->faker->optional()->randomElement($merchants),
            'payment_method' => $this->faker->randomElement($paymentMethods),
            'receipt_attachments' => $this->faker->optional(0.4)->randomElements([
                'receipt_001.jpg',
                'receipt_002.pdf'
            ]),
            'tags' => $this->faker->optional()->randomElements([
                'essential', 'luxury', 'work', 'family', 'health', 'education'
            ], 2),
            'location' => $this->faker->optional()->city(),
            'is_tax_deductible' => $this->faker->boolean(15),
            'expense_type' => $this->faker->randomElement($expenseTypes),
            'is_recurring' => $isRecurring,
            'recurring_schedule' => $isRecurring ? $this->faker->randomElement($recurringSchedules) : null,
            'budget_allocated' => $this->faker->optional()->randomFloat(2, 100, 1000),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}
