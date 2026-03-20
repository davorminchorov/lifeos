<?php

namespace App\Telegram;

use App\Ai\Agents\LifeOsAgent;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $chatId = $request->input('message.chat.id');
        $text = $request->input('message.text');

        // Ignore non-message updates (edits, callbacks, etc.)
        if (! $chatId || ! $text) {
            return response()->json(['ok' => true]);
        }

        // Only respond to the allowed chat ID
        $allowedChatId = config('telegram.allowed_chat_id');
        if ($allowedChatId && (string) $chatId !== (string) $allowedChatId) {
            Log::warning('Telegram: unauthorized chat ID', ['chat_id' => $chatId]);

            return response()->json(['ok' => true]);
        }

        try {
            $response = (new LifeOsAgent)->prompt($text);
            $this->sendMessage($chatId, (string) $response);
        } catch (\Exception $e) {
            Log::error('Telegram bot error', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId,
                'text' => $text,
            ]);
            $this->sendMessage($chatId, "Something went wrong. Please try again.\n\nError: ".$e->getMessage());
        }

        return response()->json(['ok' => true]);
    }

    protected function sendMessage(int|string $chatId, string $text): void
    {
        $token = config('nutgram.token');

        if (! $token) {
            Log::error('Telegram: TELEGRAM_TOKEN not configured');

            return;
        }

        // Telegram has a 4096 character limit per message
        $chunks = str_split($text, 4000);

        foreach ($chunks as $chunk) {
            Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $chunk,
                'parse_mode' => 'Markdown',
            ]);
        }
    }
}
