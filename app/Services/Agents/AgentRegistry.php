<?php

declare(strict_types=1);

namespace App\Services\Agents;

use Illuminate\Support\Facades\Config;
use RuntimeException;

class AgentRegistry
{
    /**
     * @var array<string, AgentDefinition>|null
     */
    private ?array $cache = null;

    public function find(string $slug): AgentDefinition
    {
        $definitions = $this->all();

        if (! isset($definitions[$slug])) {
            throw new RuntimeException("Unknown agent [{$slug}].");
        }

        return $definitions[$slug];
    }

    /**
     * @return array<string, AgentDefinition>
     */
    public function all(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $root = (string) Config::get('agents.definitions_path');

        if (! is_dir($root)) {
            return $this->cache = [];
        }

        $defs = [];

        foreach (new \DirectoryIterator($root) as $entry) {
            if ($entry->isDot() || ! $entry->isDir()) {
                continue;
            }

            $slug = $entry->getFilename();
            $jsonPath = $entry->getPathname().'/agent.json';
            $promptPath = $entry->getPathname().'/system.md';

            if (! file_exists($jsonPath) || ! file_exists($promptPath)) {
                continue;
            }

            $config = json_decode((string) file_get_contents($jsonPath), associative: true);

            if (! is_array($config)) {
                throw new RuntimeException("agents/{$slug}/agent.json is not valid JSON.");
            }

            $config['slug'] = $config['slug'] ?? $slug;
            $defs[$slug] = AgentDefinition::fromArray($config, (string) file_get_contents($promptPath));
        }

        return $this->cache = $defs;
    }

    public function isEnabled(AgentDefinition $definition): bool
    {
        $flag = $definition->featureFlag;

        return (bool) Config::get('agents.flags.'.$flag, false);
    }

    /**
     * Test seam — drop the cached definitions so a fresh load runs next.
     */
    public function flush(): void
    {
        $this->cache = null;
    }
}
