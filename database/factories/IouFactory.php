<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Iou>
 */
class IouFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['owe', 'owed'];
        $statuses = ['pending', 'partially_paid', 'paid', 'cancelled'];
        $paymentMethods = ['Cash', 'Bank Transfer', 'Credit Card', 'PayPal', 'Venmo', 'Check'];
        $categories = ['Loan', 'Borrowed Item', 'Service', 'Rent', 'Food', 'Entertainment', 'Travel', 'Gift', 'Emergency'];

        $names = [
            'John Smith', 'Jane Doe', 'Michael Johnson', 'Sarah Williams', 'David Brown',
            'Emily Davis', 'Robert Miller', 'Jennifer Wilson', 'James Moore', 'Patricia Taylor',
        ];

        $amount = $this->faker->randomFloat(2, 100, 50000);
        $amountPaid = $this->faker->randomFloat(2, 0, $amount * 0.7);
        $status = $this->faker->randomElement($statuses);
        $isRecurring = $this->faker->boolean(15);

        // Adjust amount_paid based on status
        if ($status === 'paid') {
            $amountPaid = $amount;
        } elseif ($status === 'pending') {
            $amountPaid = 0;
        } elseif ($status === 'cancelled') {
            $amountPaid = $this->faker->boolean(50) ? 0 : $this->faker->randomFloat(2, 0, $amount * 0.5);
        }

        $transactionDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $dueDate = $this->faker->optional(0.7)->dateTimeBetween($transactionDate, '+6 months');

        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $this->faker->randomElement($types),
            'person_name' => $this->faker->randomElement($names),
            'amount' => $amount,
            'currency' => 'MKD',
            'transaction_date' => $transactionDate,
            'due_date' => $dueDate,
            'description' => $this->faker->sentence(6),
            'notes' => $this->faker->optional()->paragraph(),
            'status' => $status,
            'amount_paid' => $amountPaid,
            'payment_method' => $this->faker->optional()->randomElement($paymentMethods),
            'category' => $this->faker->randomElement($categories),
            'attachments' => $this->faker->optional(0.3)->randomElements([
                'contract_001.pdf',
                'receipt_002.jpg',
                'agreement_003.pdf',
            ], 1),
            'is_recurring' => $isRecurring,
            'recurring_schedule' => $isRecurring ? $this->faker->randomElement(['monthly', 'quarterly', 'yearly']) : null,
        ];
    }

    /**
     * Indicate that the IOU is for money I owe.
     */
    public function owe(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'owe',
        ]);
    }

    /**
     * Indicate that the IOU is for money owed to me.
     */
    public function owed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'owed',
        ]);
    }

    /**
     * Indicate that the IOU is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'amount_paid' => 0,
        ]);
    }

    /**
     * Indicate that the IOU is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'amount_paid' => $attributes['amount'],
        ]);
    }

    /**
     * Indicate that the IOU is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
            'status' => $this->faker->randomElement(['pending', 'partially_paid']),
        ]);
    }
}
