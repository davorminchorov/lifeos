<?php

declare(strict_types=1);

namespace App\Services\Agents;

use Generator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Thin client for Anthropic's Managed Agents beta API.
 *
 * The class isolates the wire protocol so callers (the agents:run command,
 * tests) work against a stable PHP interface. Today every method goes through
 * raw HTTP with the `anthropic-beta: managed-agents-2026-04-01` header. As the
 * official anthropic-ai/sdk gains coverage of Managed Agents
 * endpoints, individual methods can swap in `Anthropic::beta()->...` calls
 * without callers noticing.
 *
 * `Http::fake()` in tests stands in for the real API.
 */
class ManagedAgentsClient
{
    /**
     * Create a new agent session.
     *
     * @param  array<string, mixed>  $payload  Output of AgentSessionConfig::toArray().
     * @return array{id: string, status: string} session metadata
     */
    public function createSession(array $payload): array
    {
        $response = $this->http()->post('/v1/agents/sessions', $payload);

        if ($response->failed()) {
            throw new RuntimeException(
                'Managed Agents createSession failed: '.$response->body(),
            );
        }

        $body = $response->json();

        if (! is_array($body) || empty($body['id'])) {
            throw new RuntimeException('Managed Agents createSession returned no session id.');
        }

        return [
            'id' => (string) $body['id'],
            'status' => (string) ($body['status'] ?? 'running'),
        ];
    }

    /**
     * Stream events for a session as JSON-decoded arrays. The current API
     * shape is unstable; the client treats each line of the response body as
     * a JSON event and yields its decoded payload. Tests use Http::fake() to
     * supply a canned multi-line body.
     *
     * @return Generator<int, array<string, mixed>>
     */
    public function streamEvents(string $sessionId): Generator
    {
        $response = $this->http()
            ->withOptions(['stream' => true])
            ->get('/v1/agents/sessions/'.$sessionId.'/events');

        if ($response->failed()) {
            throw new RuntimeException(
                "Managed Agents streamEvents({$sessionId}) failed: ".$response->body(),
            );
        }

        $body = $response->body();
        $lines = preg_split('/\r?\n/', $body) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, ':')) {
                continue;
            }

            // Tolerate Server-Sent-Events "data: {...}" framing as well as
            // raw newline-delimited JSON.
            if (str_starts_with($line, 'data:')) {
                $line = trim(substr($line, 5));
            }

            $event = json_decode($line, associative: true);

            if (! is_array($event)) {
                Log::warning('agents.streamEvents: skipping non-JSON event line', ['line' => $line]);

                continue;
            }

            yield $event;
        }
    }

    public function cancelSession(string $sessionId): void
    {
        $this->http()->post('/v1/agents/sessions/'.$sessionId.'/cancel')->throw();
    }

    /**
     * @return array<string, mixed>
     */
    public function getSession(string $sessionId): array
    {
        $response = $this->http()->get('/v1/agents/sessions/'.$sessionId);

        if ($response->failed()) {
            throw new RuntimeException("Managed Agents getSession({$sessionId}) failed: ".$response->body());
        }

        return (array) $response->json();
    }

    private function http(): PendingRequest
    {
        $config = (array) Config::get('agents.anthropic', []);
        $apiKey = (string) ($config['api_key'] ?? '');

        if ($apiKey === '') {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set.');
        }

        return Http::baseUrl((string) $config['base_url'])
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => (string) ($config['version'] ?? '2023-06-01'),
                'anthropic-beta' => (string) ($config['beta'] ?? 'managed-agents-2026-04-01'),
                'content-type' => 'application/json',
            ])
            ->connectTimeout((int) ($config['connect_timeout'] ?? 10))
            ->timeout((int) ($config['request_timeout'] ?? 60))
            ->acceptJson();
    }
}
