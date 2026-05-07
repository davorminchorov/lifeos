<?php

declare(strict_types=1);

namespace App\Mcp\Tools\Notifications;

use App\Mcp\Tools\AbstractTool;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

class ListNotifications extends AbstractTool
{
    protected string $name = 'notifications.list';

    protected string $description = 'List in-app notifications for the authenticated user.';

    public function schema(JsonSchema $schema): array
    {
        return [
            'unread_only' => $schema->boolean()->description('Only return unread notifications (default false).'),
            'limit' => $schema->integer()->description('Max rows (default 50, max 200).'),
        ];
    }

    public function handle(Request $request): Response|ResponseFactory
    {
        if ($error = $this->authorize()) {
            return $error;
        }

        $user = Auth::user();

        if ($user === null) {
            return Response::error('No authenticated user.');
        }

        $limit = (int) min(max((int) $request->get('limit', 50), 1), 200);

        $query = $user->notifications()->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $items = $query->limit($limit)->get()->map(fn ($n): array => [
            'id' => $n->id,
            'type' => $n->type,
            'data' => $n->data,
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at?->toIso8601String(),
        ])->all();

        return Response::structured([
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
