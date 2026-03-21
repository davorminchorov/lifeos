<?php

namespace App\Http\Controllers;

use App\Services\LifeAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(
        private readonly LifeAssistantService $assistant,
    ) {}

    /**
     * Send a message and get a response.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if (! config('prism.providers.anthropic.api_key')) {
            return response()->json([
                'error' => 'AI assistant is not configured. Please add your ANTHROPIC_API_KEY to get started.',
            ], 503);
        }

        $history = $request->session()->get('assistant_history', []);

        try {
            $reply = $this->assistant->chat($request->string('message')->toString(), $history);
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'The assistant encountered an error. Please try again.',
            ], 500);
        }

        // Persist history (cap at last 20 messages to avoid session bloat)
        $history[] = ['role' => 'user', 'content' => $request->string('message')->toString()];
        $history[] = ['role' => 'assistant', 'content' => $reply];
        $request->session()->put('assistant_history', array_slice($history, -20));

        return response()->json(['reply' => $reply]);
    }

    /**
     * Clear conversation history.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $request->session()->forget('assistant_history');

        return response()->json(['status' => 'cleared']);
    }
}
