<?php

namespace App\Ai\Tools\Ious;

use App\Ai\Tools\Concerns\ResolvesContext;
use App\Models\Iou;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class ListIousTool implements Tool
{
    use ResolvesContext;

    public function description(): string
    {
        return 'List IOUs and debts. Use when the user asks who owes them money, what they owe, or about debts.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'type' => $schema->string()->description('Filter: "owe" (I owe) or "owed" (owed to me)'),
            'status' => $schema->string()->description('Filter: pending, partially_paid, paid, cancelled. Default: pending'),
        ];
    }

    public function handle(Request $request): string
    {
        $query = Iou::where('tenant_id', $this->tenantId())
            ->where('status', $request['status'] ?? 'pending')
            ->orderBy('due_date');

        if ($request['type'] ?? null) {
            $query->where('type', $request['type']);
        }

        $ious = $query->get();

        if ($ious->isEmpty()) {
            return 'No pending IOUs found.';
        }

        $iOwe = $ious->where('type', 'owe');
        $owedToMe = $ious->where('type', 'owed');

        $lines = ['IOUs & Debts:'];

        if ($iOwe->isNotEmpty()) {
            $totalOwe = $iOwe->sum('remaining_balance');
            $lines[] = "\nI Owe ({$iOwe->count()}):";
            foreach ($iOwe as $iou) {
                $amount = $this->formatAmount($iou->remaining_balance, $iou->currency);
                $due = $iou->due_date ? ' (due '.$iou->due_date->format('M j').')' : '';
                $overdue = $iou->is_overdue ? ' [OVERDUE]' : '';
                $lines[] = "- {$iou->person_name}: {$amount}{$due}{$overdue}";
            }
            $lines[] = 'Total I owe: '.$this->formatAmount($totalOwe);
        }

        if ($owedToMe->isNotEmpty()) {
            $totalOwed = $owedToMe->sum('remaining_balance');
            $lines[] = "\nOwed to Me ({$owedToMe->count()}):";
            foreach ($owedToMe as $iou) {
                $amount = $this->formatAmount($iou->remaining_balance, $iou->currency);
                $due = $iou->due_date ? ' (due '.$iou->due_date->format('M j').')' : '';
                $overdue = $iou->is_overdue ? ' [OVERDUE]' : '';
                $lines[] = "- {$iou->person_name}: {$amount}{$due}{$overdue}";
            }
            $lines[] = 'Total owed to me: '.$this->formatAmount($totalOwed);
        }

        return implode("\n", $lines);
    }
}
