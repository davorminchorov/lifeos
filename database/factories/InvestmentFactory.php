<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Investment>
 */
class InvestmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $investmentTypes = ['stocks', 'bonds', 'etf', 'mutual_fund', 'crypto', 'real_estate', 'commodities', 'cash'];
        $stockSymbols = ['AAPL', 'GOOGL', 'MSFT', 'AMZN', 'TSLA', 'NVDA', 'META', 'NFLX', 'AMD', 'UBER'];
        $cryptoSymbols = ['BTC', 'ETH', 'ADA', 'SOL', 'DOT', 'MATIC', 'AVAX', 'LINK'];
        $etfSymbols = ['SPY', 'QQQ', 'VTI', 'VOO', 'IWM', 'EFA', 'VEA', 'BND'];
        $realEstateNames = ['Downtown Apartment', 'Suburban House', 'Commercial Property', 'Vacation Rental'];

        $investmentType = $this->faker->randomElement($investmentTypes);
        $purchaseDate = $this->faker->dateTimeBetween('-5 years', 'now');

        // Generate appropriate symbol and name based on investment type
        $symbol = null;
        $name = '';

        switch ($investmentType) {
            case 'stocks':
                $symbol = $this->faker->randomElement($stockSymbols);
                $name = $symbol.' Stock';
                break;
            case 'crypto':
                $symbol = $this->faker->randomElement($cryptoSymbols);
                $name = $symbol.' Cryptocurrency';
                break;
            case 'etf':
                $symbol = $this->faker->randomElement($etfSymbols);
                $name = $symbol.' ETF';
                break;
            case 'real_estate':
                $name = $this->faker->randomElement($realEstateNames);
                break;
            default:
                $name = $this->faker->words(2, true).' Investment';
        }

        $purchasePrice = $this->faker->randomFloat(8, 0.01, 1000);
        $quantity = $this->faker->randomFloat(8, 0.1, 1000);
        $currentValue = $purchasePrice * $this->faker->randomFloat(2, 0.5, 3.0); // -50% to +200% change

        return [
            'user_id' => 1, // Will be overridden when creating with relationships
            'investment_type' => $investmentType,
            'symbol_identifier' => $symbol,
            'name' => $name,
            'quantity' => $quantity,
            'purchase_date' => $purchaseDate,
            'purchase_price' => $purchasePrice,
            'current_value' => $currentValue,
            'total_dividends_received' => $this->faker->randomFloat(2, 0, 1000),
            'total_fees_paid' => $this->faker->randomFloat(2, 0, 100),
            'investment_goals' => $this->faker->optional()->randomElements([
                'retirement', 'growth', 'income', 'speculation', 'diversification',
            ], 2),
            'risk_tolerance' => $this->faker->randomElement(['conservative', 'moderate', 'aggressive']),
            'account_broker' => $this->faker->optional()->randomElement([
                'Fidelity', 'Charles Schwab', 'TD Ameritrade', 'E*TRADE', 'Robinhood', 'Interactive Brokers',
            ]),
            'account_number' => $this->faker->optional()->bothify('########'),
            'transaction_history' => $this->faker->optional()->randomElements([
                [
                    'date' => $purchaseDate->format('Y-m-d'),
                    'type' => 'buy',
                    'quantity' => $quantity,
                    'price' => $purchasePrice,
                ],
            ]),
            'tax_lots' => $this->faker->optional()->randomElements([
                [
                    'purchase_date' => $purchaseDate->format('Y-m-d'),
                    'quantity' => $quantity,
                    'cost_basis' => $purchasePrice,
                ],
            ]),
            'target_allocation_percentage' => $this->faker->optional()->randomFloat(2, 1, 25),
            'last_price_update' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
            'notes' => $this->faker->optional()->sentence(),
            'status' => $this->faker->randomElement(['active', 'sold', 'pending']),
        ];
    }
}
