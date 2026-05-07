<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\AgentToken;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

abstract class AbstractTool extends Tool
{
    protected function agentToken(): ?AgentToken
    {
        if (app()->bound('agent.token')) {
            $token = app('agent.token');

            return $token instanceof AgentToken ? $token : null;
        }

        $request = request();
        $token = $request->attributes->get('agent_token');

        return $token instanceof AgentToken ? $token : null;
    }

    /**
     * Verify the bound agent token may invoke this tool. Returns Response::error
     * when the token is missing or unauthorized; null otherwise.
     */
    protected function authorize(): ?Response
    {
        $token = $this->agentToken();

        if ($token === null) {
            return Response::error('No agent token bound to request.');
        }

        if (! $token->canCallTool($this->name())) {
            return Response::error(sprintf(
                'Agent token is not authorized to call [%s].',
                $this->name(),
            ));
        }

        return null;
    }
}
