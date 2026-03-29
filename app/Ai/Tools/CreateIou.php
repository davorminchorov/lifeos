<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Iou;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;

class CreateIou extends TenantScopedTool
{
    public function description(): string
    {
        return 'Create an IOU (money owed) to track debts between you and other people.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'person_name' => $schema->string()->required()->description('Name of the person'),
            'amount' => $schema->number()->required()->description('Amount of money'),
            'type' => $schema->string()->description("'owed' if they owe you, 'owe' if you owe them. Defaults to owed"),
            'reason' => $schema->string()->description('Reason for the IOU'),
            'currency' => $schema->string()->description('3-letter currency code, defaults to MKD'),
        ];
    }

    public function handle(Request $request): string
    {
        $personName = $request['person_name'] ?? null;
        $amount = $request['amount'] ?? null;
        $type = $request['type'] ?? 'owed';
        $reason = $request['reason'] ?? null;
        $currency = $request['currency'] ?? 'MKD';

        $validated = $this->validate(
            [
                'person_name' => $personName,
                'amount' => $amount,
                'type' => $type,
                'currency' => $currency,
            ],
            [
                'person_name' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01|max:99999999',
                'type' => 'required|string|in:owed,owe',
                'currency' => 'required|string|size:3',
            ],
        );

        if (is_string($validated)) {
            return $validated;
        }

        Iou::create([
            'user_id' => $this->userId,
            'tenant_id' => $this->tenantId,
            'person_name' => $personName,
            'amount' => $amount,
            'type' => $type,
            'description' => $reason,
            'currency' => $currency,
            'status' => 'pending',
            'transaction_date' => date('Y-m-d'),
        ]);

        $direction = $type === 'owe'
            ? "You owe {$personName}"
            : "{$personName} owes you";

        return "Created IOU: {$direction} {$amount} {$currency}".($reason ? " for {$reason}" : '').'.';
    }
}
