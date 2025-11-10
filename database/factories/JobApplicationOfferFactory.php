<?php

namespace Database\Factories;

use App\Enums\OfferStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplicationOffer>
 */
class JobApplicationOfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = ['USD', 'EUR', 'GBP', 'CAD'];

        $benefits = [
            'Health insurance (medical, dental, vision)',
            '401(k) with 5% company match',
            'Unlimited PTO',
            'Remote work flexibility',
            'Professional development budget',
            'Stock options',
            'Gym membership',
            'Commuter benefits',
        ];

        $equityOptions = [
            '10,000 stock options',
            '0.5% equity',
            '5,000 RSUs vesting over 4 years',
            null,
        ];

        $currency = $this->faker->randomElement($currencies);
        $baseSalary = $this->faker->numberBetween(80000, 180000);

        return [
            'user_id' => \App\Models\User::factory(),
            'job_application_id' => \App\Models\JobApplication::factory(),
            'base_salary' => $baseSalary,
            'bonus' => $this->faker->optional()->numberBetween(5000, 30000),
            'equity' => $this->faker->optional()->randomElement($equityOptions),
            'currency' => $currency,
            'benefits' => $this->faker->optional()->randomElements($benefits, $this->faker->numberBetween(3, 6)),
            'start_date' => $this->faker->optional()->dateTimeBetween('now', '+60 days'),
            'decision_deadline' => $this->faker->optional()->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(OfferStatus::cases()),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the offer is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::PENDING,
            'decision_deadline' => $this->faker->dateTimeBetween('now', '+14 days'),
        ]);
    }

    /**
     * Indicate that the offer is being negotiated.
     */
    public function negotiating(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::NEGOTIATING,
            'notes' => 'Currently negotiating salary and benefits package.',
        ]);
    }

    /**
     * Indicate that the offer has been accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::ACCEPTED,
            'start_date' => $this->faker->dateTimeBetween('+7 days', '+30 days'),
        ]);
    }

    /**
     * Indicate that the offer has been declined.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::DECLINED,
            'notes' => 'Declined due to better offer elsewhere.',
        ]);
    }

    /**
     * Indicate that the offer has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OfferStatus::EXPIRED,
            'decision_deadline' => $this->faker->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    /**
     * Indicate that the offer includes a high salary.
     */
    public function highSalary(): static
    {
        return $this->state(fn (array $attributes) => [
            'base_salary' => $this->faker->numberBetween(150000, 250000),
            'bonus' => $this->faker->numberBetween(20000, 50000),
        ]);
    }

    /**
     * Indicate that the offer includes equity.
     */
    public function withEquity(): static
    {
        return $this->state(fn (array $attributes) => [
            'equity' => $this->faker->randomElement([
                '10,000 stock options vesting over 4 years',
                '1% equity',
                '15,000 RSUs',
            ]),
        ]);
    }
}
