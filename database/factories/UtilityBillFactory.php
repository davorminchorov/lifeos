<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UtilityBill>
 */
class UtilityBillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $utilityTypes = ['electricity', 'gas', 'water', 'internet', 'phone', 'cable_tv', 'trash', 'sewer', 'other'];

        $providersByType = [
            'electricity' => ['PG&E', 'ConEd', 'Duke Energy', 'Florida Power & Light', 'ComEd'],
            'gas' => ['PG&E Gas', 'ConEd Gas', 'Atmos Energy', 'CenterPoint Energy', 'National Grid'],
            'water' => ['City Water Department', 'Municipal Water', 'Regional Water Authority', 'Water District'],
            'internet' => ['Comcast Xfinity', 'Verizon Fios', 'AT&T', 'Spectrum', 'Cox Communications'],
            'phone' => ['Verizon', 'AT&T', 'T-Mobile', 'Sprint', 'Cricket Wireless'],
            'cable_tv' => ['Comcast', 'DirecTV', 'Dish Network', 'Spectrum TV', 'Cox Cable'],
            'trash' => ['Waste Management', 'Republic Services', 'City Sanitation', 'Local Waste Co'],
            'sewer' => ['City Sewer Department', 'Municipal Sewer', 'Regional Sewer Authority'],
            'other' => ['Utility Company', 'Service Provider', 'Municipal Services']
        ];

        $usageUnitsByType = [
            'electricity' => 'kWh',
            'gas' => 'therms',
            'water' => 'gallons',
            'internet' => 'GB',
            'phone' => 'minutes',
            'cable_tv' => null,
            'trash' => null,
            'sewer' => 'gallons',
            'other' => null
        ];

        $utilityType = $this->faker->randomElement($utilityTypes);
        $provider = $this->faker->randomElement($providersByType[$utilityType]);
        $usageUnit = $usageUnitsByType[$utilityType];

        $billPeriodStart = $this->faker->dateTimeBetween('-3 months', '-1 month');
        $billPeriodEnd = (clone $billPeriodStart)->modify('+1 month');
        $dueDate = (clone $billPeriodEnd)->modify('+2 weeks');

        // Generate usage and billing data based on utility type
        $usageAmount = null;
        $ratePerUnit = null;
        $billAmount = $this->faker->randomFloat(2, 25, 500);

        if ($usageUnit) {
            switch ($utilityType) {
                case 'electricity':
                    $usageAmount = $this->faker->randomFloat(2, 200, 1500);
                    $ratePerUnit = $this->faker->randomFloat(4, 0.08, 0.25);
                    break;
                case 'gas':
                    $usageAmount = $this->faker->randomFloat(2, 10, 200);
                    $ratePerUnit = $this->faker->randomFloat(4, 0.80, 1.50);
                    break;
                case 'water':
                    $usageAmount = $this->faker->randomFloat(2, 1000, 10000);
                    $ratePerUnit = $this->faker->randomFloat(6, 0.003, 0.015);
                    break;
                case 'internet':
                    $usageAmount = $this->faker->randomFloat(2, 100, 2000);
                    break;
                case 'sewer':
                    $usageAmount = $this->faker->randomFloat(2, 500, 5000);
                    $ratePerUnit = $this->faker->randomFloat(6, 0.002, 0.010);
                    break;
            }
        }

        return [
            'user_id' => 1, // Will be overridden when creating with relationships
            'utility_type' => $utilityType,
            'service_provider' => $provider,
            'account_number' => $this->faker->bothify('##########'),
            'service_address' => $this->faker->address(),
            'bill_amount' => $billAmount,
            'usage_amount' => $usageAmount,
            'usage_unit' => $usageUnit,
            'rate_per_unit' => $ratePerUnit,
            'bill_period_start' => $billPeriodStart,
            'bill_period_end' => $billPeriodEnd,
            'due_date' => $dueDate,
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'overdue', 'disputed']),
            'payment_date' => $this->faker->optional(0.7)->dateTimeBetween($billPeriodEnd, $dueDate),
            'meter_readings' => $this->faker->optional()->randomElements([
                [
                    'current' => $this->faker->numberBetween(10000, 99999),
                    'previous' => $this->faker->numberBetween(5000, 10000),
                    'date' => $billPeriodEnd->format('Y-m-d')
                ]
            ]),
            'bill_attachments' => $this->faker->optional(0.5)->randomElements([
                'utility_bill_001.pdf',
                'statement_002.jpg'
            ]),
            'service_plan' => $this->faker->optional()->words(3, true),
            'contract_terms' => $this->faker->optional()->sentence(),
            'auto_pay_enabled' => $this->faker->boolean(60),
            'usage_history' => $this->faker->optional()->randomElements([
                ['month' => 'Jan', 'usage' => 500, 'cost' => 75],
                ['month' => 'Feb', 'usage' => 450, 'cost' => 68],
                ['month' => 'Mar', 'usage' => 520, 'cost' => 78]
            ]),
            'budget_alert_threshold' => $this->faker->optional()->randomFloat(2, 100, 300),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
