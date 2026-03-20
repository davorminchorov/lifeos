<?php

namespace App\Ai\Tools\Ious;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Iou;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class AddIouTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'Track money owed. Use when the user lent money to someone, borrowed money, or needs to track a debt.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('"owe" if I owe them, "owed" if they owe me')->required(),
            'person_name' => $schema->string()->description('Name of the person')->required(),
            'amount' => $schema->number()->description('Amount as a decimal')->required(),
            'currency' => $schema->string()->description('Currency code (default: MKD)'),
            'description' => $schema->string()->description('What the debt is for'),
            'due_date' => $schema->string()->description('Due date in Y-m-d format'),
        ];
    }

    public function handle(Request $request): string
    {
        $iou = Iou::create([
            'tenant_id' => $this->tenantId(),
            'user_id' => $this->userId(),
            'type' => $request['type'],
            'person_name' => $request['person_name'],
            'amount' => $request['amount'],
            'currency' => $request['currency'] ?? $this->defaultCurrency(),
            'description' => $request['description'] ?? null,
            'transaction_date' => now()->toDateString(),
            'due_date' => $request['due_date'] ?? null,
            'status' => 'pending',
            'amount_paid' => 0,
        ]);

        $amount = $this->formatAmount($iou->amount, $iou->currency);
        $direction = $iou->type === 'owe' ? "You owe {$iou->person_name}" : "{$iou->person_name} owes you";

        return "IOU created: {$direction} {$amount}".($iou->description ? " for {$iou->description}" : '');
    }
}
