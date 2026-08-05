<?php

namespace App\Ai;

use App\Contracts\AiClient;
use RuntimeException;

/**
 * Stand-in for the real provider. Fabricates a valid response by walking the
 * request's schema, so any future operation gets a working fake for free —
 * and a schema this walker chokes on would have choked Gemini too.
 *
 * Fails on command when the string "fail" appears in any text part. That is
 * the only way to exercise the refund path, since a real API cannot be made
 * to fail on demand.
 */
class FakeAiClient implements AiClient
{
    public function send(AiRequest $request): AiResponse
    {
        sleep((int)config('services.ai.fake_delay', 3));

        foreach ($request->parts as $part) {
            if ($part->text !== null && stripos($part->text, 'fail') !== false) {
                throw new RuntimeException('Fake AI client was told to fail.');
            }
        }

        $rate = (float)config('services.ai.fake_failure_rate', 0.0);

        if ($rate > 0 && mt_rand() / mt_getrandmax() < $rate) {
            throw new RuntimeException('Fake AI client failed randomly.');
        }

        return new AiResponse(
            data: $this->fabricate($request->schema),
            tokensUsed: 1234,
            model: 'fake',
        );
    }

    private function fabricate(array $schema, string $name = 'value'): mixed
    {
        if (isset($schema['enum'])) {
            return $schema['enum'][0];
        }

        return match ($schema['type'] ?? null) {
            'object' => $this->fabricateObject($schema),
            'array' => $this->fabricateArray($schema, $name),
            'string' => str_contains($name, 'date') ? '2027-01-01' : "Fake {$name}",
            'number' => 100,
            'integer' => 2,
            'boolean' => true,
            default => throw new RuntimeException("FakeAiClient cannot fabricate '{$name}': unsupported schema type."),
        };
    }

    private function fabricateObject(array $schema): array
    {
        $out = [];

        foreach ($schema['properties'] ?? [] as $key => $property) {
            $out[$key] = $this->fabricate($property, $key);
        }

        return $out;
    }

    private function fabricateArray(array $schema, string $name): array
    {
        $items = $schema['items'] ?? throw new RuntimeException("FakeAiClient cannot fabricate '{$name}': array without items.");

        $count = min(max(2, $schema['minItems'] ?? 0), $schema['maxItems'] ?? PHP_INT_MAX);

        return array_map(
            fn($i) => $this->fabricate($items, "{$name} {$i}"),
            range(1, $count),
        );
    }
}
