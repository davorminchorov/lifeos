<?php

namespace App\Ai\Tools\System;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Artisan;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

class SyncAllTool implements Tool
{
    public function description(): string
    {
        return 'Run all scheduled sync and check commands. Use when the user says /sync or asks to refresh data.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }

    public function handle(Request $request): string
    {
        $commands = [
            'subscriptions:check-renewals' => 'Check subscription renewals',
            'utility-bills:check-due' => 'Check utility bills due',
            'warranties:check-expiration' => 'Check warranty expirations',
            'contracts:check-expiration' => 'Check contract expirations',
            'subscriptions:update-next-billing' => 'Update subscription billing dates',
        ];

        $results = [];

        foreach ($commands as $command => $label) {
            try {
                Artisan::call($command);
                $results[] = "- {$label}: OK";
            } catch (\Exception $e) {
                $results[] = "- {$label}: Failed ({$e->getMessage()})";
            }
        }

        return "Sync completed:\n".implode("\n", $results);
    }
}
