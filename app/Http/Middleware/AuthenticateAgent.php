<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AgentToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $this->extractToken($request);

        if ($plain === null) {
            return response()->json(['error' => 'Missing bearer token.'], 401);
        }

        $token = AgentToken::resolve($plain);

        if ($token === null) {
            return response()->json(['error' => 'Invalid or expired token.'], 401);
        }

        $user = $token->user;

        if ($user === null) {
            return response()->json(['error' => 'Token user no longer exists.'], 401);
        }

        $user->forceFill(['current_tenant_id' => $token->tenant_id]);
        Auth::setUser($user);

        $request->attributes->set('agent_token', $token);
        App::instance('agent.token', $token);

        $token->recordUse();

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->bearerToken();

        if (is_string($header) && $header !== '') {
            return $header;
        }

        return null;
    }
}
