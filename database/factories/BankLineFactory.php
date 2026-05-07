<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BankLine;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BankLine>
 */
class BankLineFactory extends Factory
{
    protected $model = BankLine::class;

    public function definition(): array
    {
        $merchant = $this->faker->company();
        $account = 'TEST****'.$this->faker->numberBetween(1000, 9999);
        $postedAt = $this->faker->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
        $amountCents = -1 * $this->faker->numberBetween(100, 50000);
        $currency = 'EUR';

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'created_by_agent_token_id' => null,
            'account' => $account,
            'posted_at' => $postedAt,
            'amount_cents' => $amountCents,
            'currency' => $currency,
            'merchant_raw' => $merchant,
            'description' => $merchant.' - card purchase',
            'fingerprint' => hash('sha256', Str::random(16)),
            'match_status' => BankLine::STATUS_UNMATCHED,
            'source' => 'agent',
        ];
    }
}
