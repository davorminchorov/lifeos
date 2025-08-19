<?php

namespace Database\Factories;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvestmentDividend>
 */
class InvestmentDividendFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dividendTypes = ['ordinary', 'qualified', 'special', 'return_of_capital'];
        $frequencies = ['monthly', 'quarterly', 'semi_annual', 'annual', 'special'];
        $currencies = ['USD', 'EUR', 'MKD'];

        $recordDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $paymentDate = (clone $recordDate)->modify('+' . $this->faker->numberBetween(1, 30) . ' days');
        $exDividendDate = (clone $recordDate)->modify('-' . $this->faker->numberBetween(1, 5) . ' days');

        $sharesHeld = $this->faker->randomFloat(8, 1, 1000);
        $dividendPerShare = $this->faker->randomFloat(8, 0.01, 5.00);
        $amount = $sharesHeld * $dividendPerShare;
        $taxWithheld = $this->faker->randomFloat(2, 0, $amount * 0.3);

        return [
            'investment_id' => Investment::factory(),
            'amount' => $amount,
            'record_date' => $recordDate,
            'payment_date' => $paymentDate,
            'ex_dividend_date' => $exDividendDate,
            'dividend_type' => $this->faker->randomElement($dividendTypes),
            'frequency' => $this->faker->randomElement($frequencies),
            'dividend_per_share' => $dividendPerShare,
            'shares_held' => $sharesHeld,
            'tax_withheld' => $taxWithheld,
            'currency' => $this->faker->randomElement($currencies),
            'reinvested' => $this->faker->boolean(40),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }
}
