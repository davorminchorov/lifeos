<?php

namespace App\Telegram\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeTelegramUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $chatId = $request->input('message.chat.id');
        $allowedChatId = config('telegram.allowed_chat_id');

        if ($allowedChatId && (string) $chatId !== (string) $allowedChatId) {
            Log::warning('Telegram: unauthorized access attempt', [
                'chat_id' => $chatId,
            ]);

            return response()->json(['ok' => true], 200);
        }

        return $next($request);
    }
}
