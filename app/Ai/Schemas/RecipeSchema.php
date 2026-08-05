<?php

namespace App\Ai\Schemas;

use App\Enums\Unit;

/**
 * Response schema for recipe generation. Sent as the API's responseSchema —
 * the schema, not the prompt, is the authority on field names and legal
 * values.
 */
class RecipeSchema
{
    public static function get(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => 'Short recipe name.',
                ],
                'description' => [
                    'type' => 'string',
                    'nullable' => true,
                    'description' => 'One or two sentences describing the dish.',
                ],
                'servings' => [
                    'type' => 'integer',
                    'nullable' => true,
                ],
                'prep_time' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'description' => 'Preparation time in minutes.',
                ],
                'cook_time' => [
                    'type' => 'integer',
                    'nullable' => true,
                    'description' => 'Cooking time in minutes.',
                ],
                'ingredients' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 30,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                            ],
                            'quantity' => [
                                'type' => 'number',
                                'nullable' => true,
                                'description' => 'Empty when there is no basis for a number.',
                            ],
                            'unit' => [
                                'type' => 'string',
                                'enum' => Unit::values(),
                                'nullable' => true,
                            ],
                            'notes' => [
                                'type' => 'string',
                                'nullable' => true,
                                'description' => 'Amount wording that fits no unit ("a pinch", "to taste") or short preparation notes.',
                            ],
                        ],
                        'required' => ['name'],
                    ],
                ],
                'instructions' => [
                    'type' => 'array',
                    'minItems' => 1,
                    'maxItems' => 30,
                    'items' => [
                        'type' => 'string',
                        'description' => 'One step, in cooking order.',
                    ],
                ],
            ],
            'required' => ['name', 'ingredients', 'instructions'],
        ];
    }
}
