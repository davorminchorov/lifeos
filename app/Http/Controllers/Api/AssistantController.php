<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Ai\Agents\LifeOsAssistant;
use App\Http\Controllers\Controller;
use App\Services\AssistantContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AssistantController extends Controller
{
    /**
     * Send a message to the AI assistant.
     */
    public function message(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:5000',
                'conversation_id' => 'nullable|string',
            ]);

            $user = auth()->user();

            $agent = new LifeOsAssistant($user, new AssistantContextService);
            $agent->withPage($request->header('X-Current-Page', ''));

            if (! empty($validated['conversation_id'])) {
                $response = $agent->continue($validated['conversation_id'], as: $user)->prompt($validated['message']);
            } else {
                $response = $agent->forUser($user)->prompt($validated['message']);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'message' => (string) $response,
                    'conversation_id' => $response->conversationId,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Assistant message failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "I'm having trouble right now. Please try again.",
            ], 500);
        }
    }

    /**
     * Get contextual suggestions for the assistant.
     */
    public function suggestions(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }

    /**
     * Get conversation history for the authenticated user.
     */
    public function history(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [],
        ]);
    }
}
