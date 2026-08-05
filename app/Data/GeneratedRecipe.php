<?php

namespace App\Data;

use RuntimeException;

final readonly class GeneratedRecipe
{
    /**
     * @param array<int, array{name: string, quantity: float|null, unit: string|null, notes: string|null}> $ingredients
     * @param array<int, string> $instructions
     */

    public function __construct(
        public string  $name,
        public ?string $description,
        public ?int    $servings,
        public ?int    $prepTime,
        public ?int    $cookTime,
        public array   $ingredients,
        public array   $instructions,
        public ?int    $tokensUsed = null,
    )
    {
    }

    /**
     * Build from a schema-shaped response. The schema already constrains the
     * shape; this is the belt-and-braces pass that coerces types and rejects
     * an answer too empty to be worth saving.
     *
     * @throws RuntimeException when the data is unusable
     */
    public static function fromArray(array $data): self
    {
        $name = trim((string)($data['name'] ?? ''));

        if ($name === '') {
            throw new RuntimeException('Generated recipe has no name.');
        }

        $ingredients = [];
        $seen = [];

        foreach ((array)($data['ingredients'] ?? []) as $ingredient) {
            if (!is_array($ingredient) || trim((string)($ingredient['name'] ?? '')) === '') {
                continue;
            }

            // The schema cannot forbid the model repeating an ingredient;
            // first occurrence wins.
            $key = mb_strtolower(trim((string)$ingredient['name']));

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $quantity = $ingredient['quantity'] ?? null;
            $unit = trim((string)($ingredient['unit'] ?? ''));
            $notes = trim((string)($ingredient['notes'] ?? ''));

            $ingredients[] = [
                'name' => trim((string)$ingredient['name']),
                'quantity' => is_numeric($quantity) ? (float)$quantity : null,
                'unit' => $unit !== '' ? $unit : null,
                'notes' => $notes !== '' ? $notes : null,
            ];
        }

        if ($ingredients === []) {
            throw new RuntimeException('Generated recipe has no ingredients.');
        }

        $instructions = array_values(array_filter(
            array_map(fn($step) => trim((string)$step), (array)($data['instructions'] ?? [])),
            fn(string $step) => $step !== '',
        ));

        if ($instructions === []) {
            throw new RuntimeException('Generated recipe has no instructions.');
        }

        $description = trim((string)($data['description'] ?? ''));

        return new self(
            name: $name,
            description: $description !== '' ? $description : null,
            servings: is_numeric($data['servings'] ?? null) ? (int)$data['servings'] : null,
            prepTime: is_numeric($data['prep_time'] ?? null) ? (int)$data['prep_time'] : null,
            cookTime: is_numeric($data['cook_time'] ?? null) ? (int)$data['cook_time'] : null,
            ingredients: $ingredients,
            instructions: $instructions,
        );
    }
}
