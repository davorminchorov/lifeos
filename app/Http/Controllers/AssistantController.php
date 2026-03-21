<?php

namespace App\Http\Controllers;

use App\Ai\Agents\LifeAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    /**
     * Send a message to the Life Assistant and get a response.
     * Uses DB-backed conversation history via RemembersConversations.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'conversation_id' => ['nullable', 'string', 'uuid'],
        ]);

        if (! config('ai.providers.anthropic.key')) {
            return response()->json([
                'error' => 'AI assistant is not configured. Add ANTHROPIC_API_KEY to your .env to get started.',
            ], 503);
        }

        try {
            $agent = new LifeAssistant;
            $conversationId = $request->input('conversation_id');

            $response = $conversationId
                ? $agent->continue($conversationId, as: $request->user())->prompt($request->string('message')->toString())
                : $agent->forUser($request->user())->prompt($request->string('message')->toString());
        } catch (\Exception $e) {
            report($e);

            return response()->json([
                'error' => 'The assistant encountered an error. Please try again.',
            ], 500);
        }

        return response()->json([
            'reply' => $response->text,
            'conversation_id' => $response->conversationId,
        ]);
    }

    /**
     * Delete the conversation history for the given conversation.
     */
    public function clearHistory(Request $request): JsonResponse
    {
        // Conversation history is managed by laravel/ai in the DB.
        // The frontend simply drops its conversation_id to start fresh.
        return response()->json(['status' => 'cleared']);
    }
}
