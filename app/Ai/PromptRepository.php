<?php

namespace App\Ai;

use RuntimeException;

class PromptRepository
{
    /**
     * Prompts already read. Registered as a singleton, so each file is read
     * from disk at most once per request.
     *
     * @var array<string, string>
     */
    private array $loaded = [];

    public function get(string $name): string
    {
        return $this->loaded[$name] ??= $this->read($name);
    }

    /**
     * Short content hash, written into request_meta so every generation can
     * be traced back to the exact prompt text that produced it.
     */
    public function version(string $name): string
    {
        return substr(sha1($this->get($name)), 0, 8);
    }

    private function read(string $name): string
    {
        $path = resource_path("prompts/{$name}.md");

        if (!is_file($path)) {
            throw new RuntimeException("Prompt file missing: {$path}");
        }

        return trim(file_get_contents($path));
    }
}
