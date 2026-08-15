<?php

namespace App\Ai\Schemas;

/**
 * Response schema for a photo turned into pantry items.
 *
 * Deliberately smaller than RecipeSchema: a photo cannot tell you how much of
 * something there is, and it certainly cannot read an expiry date printed in
 * 6pt on a lid facing away. Asking for those would get plausible numbers back,
 * which is worse than getting none — a wrong expiry date silently drives the
 * whole expiry-warning feature.
 */
class PantryItemsSchema
{
    /** A shelf photo that yields more than this is a photo of a supermarket. */
    private const MAX_ITEMS = 40;

    /**
     * @param int[] $spaceIds the user's own storage spaces. They become the
     *              schema enum, so the model cannot assign an item to a space
     *              that is not theirs — the same guarantee RecipeSchema gets
     *              from the submitted ingredient names.
     */
    public static function get(array $spaceIds): array
    {
        $space = [
            'type' => 'string',
            'nullable' => true,
            'description' => 'Id of the storage space this belongs in, from the list provided with the photo. Empty when none clearly fits.',
        ];

        // Gemini only honours enum on a string, which is why the ids travel as
        // strings and are cast back on the way in.
        if ($spaceIds !== []) {
            $space['enum'] = array_values(array_map('strval', array_unique($spaceIds)));
        }

        return [
            'type' => 'object',
            'properties' => [
                'items' => [
                    'type' => 'array',
                    'maxItems' => self::MAX_ITEMS,
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'Everyday name for the item, as someone would write it on a shopping list.',
                            ],
                            'space_id' => $space,
                            'notes' => [
                                'type' => 'string',
                                'nullable' => true,
                                'description' => 'Short observation worth keeping, such as "large bottle" or "half empty". Empty when there is nothing to add.',
                            ],
                        ],
                        'required' => ['name'],
                    ],
                ],
            ],
            'required' => ['items'],
        ];
    }
}
